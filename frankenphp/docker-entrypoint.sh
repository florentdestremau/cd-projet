#!/bin/sh
set -e

mkdir -p /storage/sessions /storage/uploads

php bin/console doctrine:migrations:migrate --no-interaction

# Load fixtures if database is empty (first boot)
USER_COUNT=$(php bin/console doctrine:query:sql "SELECT COUNT(*) AS c FROM users" --quiet --format=json 2>/dev/null | grep -oE '"c":[0-9]+' | head -1 | grep -oE '[0-9]+' || echo "0")
if [ "$USER_COUNT" = "0" ]; then
    echo "Empty database, loading fixtures…"
    php bin/console doctrine:fixtures:load --no-interaction --append || echo "Fixtures load skipped"
fi

exec "$@"
