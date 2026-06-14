# 02 — Stack technique

## Vue d'ensemble

| Couche | Technologie | Version |
|---|---|---|
| Runtime | FrankenPHP (Caddy + PHP + Mercure embarqués) | `dunglas/frankenphp:1-php8.4-alpine` |
| Langage | PHP | 8.4 |
| Framework | Symfony | 8.0.* (preview) |
| Templating | Twig | 3.x |
| Frontend | AssetMapper + Stimulus + Turbo (Hotwire) | latest |
| CSS | Custom artisanal, variables CSS | — |
| Polices | Cormorant Garamond (titres) + Inter (corps) | Google Fonts via `<link>` |
| BDD | SQLite (WAL mode) | via `pdo_sqlite` |
| ORM | Doctrine ORM | 3.x |
| Migrations | Doctrine Migrations Bundle | 4.x |
| Messenger | Symfony Messenger transport Doctrine | — |
| Temps réel | Mercure (intégré FrankenPHP en prod, container en dev) | — |
| Mail | Symfony Mailer + Mailpit en dev | — |
| Notifs navigateur | Symfony Notifier + WebPush bridge | — |
| Auth | Symfony Security, `form_login`, hash argon2id | — |
| Fichiers | VichUploaderBundle → `/storage/uploads/` | — |
| PDF | Dompdf via `dompdf/dompdf` | — |
| Exports | CSV via Symfony Serializer | — |
| Tests | PHPUnit + Symfony Test Pack | — |
| Quality | PHPStan, Rector, PHP-CS-Fixer, Twig-CS-Fixer | — |

## Principes structurants

### No-build
- AssetMapper sert le JS et le CSS en clair, importmap pour les dépendances JS.
- Aucun bundler, aucun Node, aucun npm. Toute la chaîne reste PHP.
- Build Docker exécute `importmap:install` puis `asset-map:compile` pour
  pré-générer les digests en prod.

### Stimulus + Turbo, pas de SPA
- Turbo Drive pour la navigation full-page sans rechargement.
- Turbo Frames pour les zones rechargeables (fil de commentaires, etc.).
- Turbo Streams (sur Mercure) pour les mises à jour live du dashboard et fil
  d'activité.
- Stimulus pour les comportements riches (drag & drop Kanban, autocomplete
  mentions, etc.).

### Pourquoi SQLite
- Volumétrie cible (300 projets actifs, ~10 users simultanés) très en deçà des
  limites de SQLite.
- Mode WAL activé pour permettre concurrence lecture/écriture.
- Backup = copie d'un fichier (`/storage/data_prod.db`).
- Stocké sur volume persistant `/storage`, comme dans le repo `ag-voter-sf`.

### Pourquoi FrankenPHP
- Caddy + PHP + Mercure en un seul binaire → une seule image en prod.
- Pas de container Mercure séparé en production.
- HTTPS automatique si on l'active (ici délégué au reverse proxy du serveur).

## Dépendances Composer cibles (require)

```
doctrine/doctrine-bundle
doctrine/doctrine-migrations-bundle
doctrine/orm
dompdf/dompdf
symfony/asset-mapper
symfony/console
symfony/dotenv
symfony/form
symfony/framework-bundle
symfony/mailer
symfony/mercure-bundle
symfony/messenger
symfony/notifier
symfony/runtime
symfony/security-bundle
symfony/stimulus-bundle
symfony/twig-bundle
symfony/ux-turbo
symfony/validator
symfony/web-push (bridge notifier)
vich/uploader-bundle
twig/extra-bundle
```

## Dépendances dev

```
doctrine/doctrine-fixtures-bundle
fakerphp/faker
friendsofphp/php-cs-fixer
phpstan/phpstan + phpstan-doctrine
phpunit/phpunit
rector/rector
symfony/maker-bundle
symfony/web-profiler-bundle
vincentlanglet/twig-cs-fixer
```

## Frontend (importmap)

Pas de Bootstrap. Importmap minimaliste :

```php
return [
    'app' => ['path' => './assets/app.js', 'entrypoint' => true],
    '@hotwired/stimulus' => ['version' => '3.x'],
    '@hotwired/turbo' => ['version' => '7.x'],
    '@symfony/stimulus-bundle' => ['path' => '...'],
    // contrôleurs métier ajoutés au fur et à mesure
];
```

CSS dans `assets/styles/app.css` avec variables et organisation modulaire
(reset, typo, layout, composants, pages).
