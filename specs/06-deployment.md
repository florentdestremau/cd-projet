# 06 — Déploiement

## Cible

**Serveur Once de l'utilisateur** (auto-hébergé). Déploiement par image
Docker tirée depuis GitHub Container Registry à chaque push sur `master`.

## Architecture image

Une seule image FrankenPHP qui contient :
- PHP 8.4 + extensions (`intl`, `opcache`, `pdo_sqlite`, `zip`)
- Caddy + plugin Mercure intégrés (FrankenPHP)
- Le code Symfony pré-compilé (`asset-map:compile`, `cache:warmup --env=prod`)

**Pas de container Mercure séparé en prod.** Le hub Mercure tourne dans le
même processus que le serveur web via le plugin Caddy.

## Volume `/storage`

Volume persistant monté sur `/storage`, contient :

```
/storage/
├── data_prod.db          # SQLite principal
├── data_prod.db-wal      # WAL mode
├── data_prod.db-shm      # WAL shared memory
├── sessions/             # sessions PHP
└── uploads/              # fichiers Vich (croquis, photos, PDF générés)
```

L'image est **stateless**, tout l'état est dans le volume. Backup = snapshot
du volume.

## Dockerfile (squelette repris d'`ag-voter-sf`)

```dockerfile
ARG APP_VERSION=unknown
ARG BUILD_DATE=unknown

FROM composer:2 AS composer
FROM dunglas/frankenphp:1-php8.4-alpine AS base

WORKDIR /app
RUN apk add --no-cache acl && \
    install-php-extensions intl opcache pdo_sqlite zip

COPY --link frankenphp/Caddyfile /etc/caddy/Caddyfile
COPY --link frankenphp/conf.d/app.ini $PHP_INI_DIR/conf.d/app.ini

FROM base AS builder
COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV APP_ENV=prod APP_DEBUG=0 APP_SECRET=buildsecret
COPY --link composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-progress
COPY --link . .
RUN composer dump-autoload --optimize --no-dev && \
    php bin/console importmap:install && \
    php bin/console asset-map:compile && \
    php bin/console cache:warmup --env=prod

FROM base AS final
ARG APP_VERSION
ARG BUILD_DATE
ENV APP_ENV=prod APP_DEBUG=0 \
    DATABASE_URL="sqlite:////storage/data_prod.db" \
    APP_VERSION=${APP_VERSION} BUILD_DATE=${BUILD_DATE}

RUN addgroup -S -g 1000 php && adduser -S -u 1000 -G php php && \
    mkdir -p /storage /data/caddy /config/caddy && \
    chown -R 1000:1000 /storage /data /config

USER 1000:1000
VOLUME /storage

COPY --chown=1000:1000 --from=builder --link /app /app

ENTRYPOINT ["/app/frankenphp/docker-entrypoint.sh"]
EXPOSE 80
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
```

## Caddyfile (avec Mercure intégré)

```caddy
{
    frankenphp
    auto_https off
    admin off
    persist_config off
}

:80 {
    log {
        format filter {
            request>uri query { replace authorization REDACTED }
        }
    }
    root /app/public
    encode zstd br gzip

    mercure {
        publisher_jwt {env.MERCURE_JWT_SECRET} HS256
        subscriber_jwt {env.MERCURE_JWT_SECRET} HS256
        anonymous
        subscriptions
    }

    @phpRoute {
        not path /.well-known/mercure*
        not file {path}
    }
    rewrite @phpRoute index.php

    @frontController path index.php
    php @frontController

    file_server { hide *.php }
}
```

## Entrypoint

```sh
#!/bin/sh
set -e
mkdir -p /storage/sessions /storage/uploads
php bin/console doctrine:migrations:migrate --no-interaction
exec "$@"
```

Migrations rejouées à chaque démarrage (idempotent grâce à Doctrine
Migrations).

## Compose de développement

`compose.yaml` (Mercure standalone pour dev local sans FrankenPHP) :

```yaml
services:
  mercure:
    image: dunglas/mercure
    environment:
      SERVER_NAME: ':80'
      MERCURE_PUBLISHER_JWT_KEY: '!ChangeThisMercureHubJWTSecretKey!'
      MERCURE_SUBSCRIBER_JWT_KEY: '!ChangeThisMercureHubJWTSecretKey!'
      MERCURE_EXTRA_DIRECTIVES: |
        cors_origins http://127.0.0.1:8000 http://localhost:8000
        anonymous
    command: /usr/bin/caddy run --config /etc/caddy/dev.Caddyfile
    volumes:
      - mercure_data:/data
      - mercure_config:/config

volumes:
  mercure_data:
  mercure_config:
```

`compose.override.yaml` (Mailpit + ports dev) :

```yaml
services:
  mailer:
    image: axllent/mailpit
    ports: ["1025", "8025"]
    environment:
      MP_SMTP_AUTH_ACCEPT_ANY: 1
      MP_SMTP_AUTH_ALLOW_INSECURE: 1
  mercure:
    ports: ["3001:80"]
```

## CI GitHub

Deux workflows.

### `.github/workflows/ci.yml`
Lint matrix + tests à chaque push et PR :
- PHPStan
- Rector (`--dry-run`)
- PHP-CS-Fixer (`--dry-run --diff`)
- Twig-CS-Fixer
- PHPUnit avec migrations sur SQLite test

### `.github/workflows/docker.yml`
Build et push de l'image vers `ghcr.io/<user>/<repo>` :
- Triggers : push sur `master`, tags `v*`, PRs vers `master`
- Tags : `branch`, `pr-N`, `vX.Y`, `vX`, `sha-XXXXXXX`
- Cache buildx via `buildcache` tag
- Build args `APP_VERSION` (sha court) et `BUILD_DATE`

## Déploiement sur serveur Once

Procédure manuelle au premier déploiement, puis script de mise à jour :

```bash
# Premier déploiement
docker volume create cd-projet-storage
docker run -d \
  --name cd-projet \
  --restart unless-stopped \
  -v cd-projet-storage:/storage \
  -p 8080:80 \
  -e APP_SECRET=<secret_long_random> \
  -e MERCURE_JWT_SECRET=<jwt_secret_long_random> \
  -e MERCURE_URL=https://cd-projet.example.com/.well-known/mercure \
  -e MERCURE_PUBLIC_URL=https://cd-projet.example.com/.well-known/mercure \
  -e MAILER_DSN=smtp://... \
  -e DEFAULT_URI=https://cd-projet.example.com \
  ghcr.io/florentdestremau/cd-projet:master

# Mise à jour
docker pull ghcr.io/florentdestremau/cd-projet:master
docker stop cd-projet && docker rm cd-projet
docker run -d ... (idem que ci-dessus)
```

Le reverse proxy du serveur Once gère HTTPS et forward vers `:8080`.

## Backups

- Cron quotidien sur le serveur : `sqlite3 /storage/data_prod.db ".backup
  /backups/cd-projet-$(date +%Y%m%d).db"`.
- Rétention 30 jours locale + synchro vers stockage externe (à définir par
  l'utilisateur).
- Backup des uploads via rsync du dossier `/storage/uploads/`.

## Variables d'environnement de prod

| Variable | Valeur |
|---|---|
| `APP_ENV` | `prod` |
| `APP_DEBUG` | `0` |
| `APP_SECRET` | random 64+ chars |
| `DATABASE_URL` | `sqlite:////storage/data_prod.db` |
| `DEFAULT_URI` | URL publique HTTPS |
| `MERCURE_URL` | `<url>/.well-known/mercure` |
| `MERCURE_PUBLIC_URL` | idem (vu côté navigateur) |
| `MERCURE_JWT_SECRET` | random 64+ chars |
| `MAILER_DSN` | SMTP serveur (à fournir) |
| `ADMIN_INITIAL_EMAIL` | email du premier admin créé au boot |
| `ADMIN_INITIAL_PASSWORD_HASH` | hash argon2id du mot de passe initial |
