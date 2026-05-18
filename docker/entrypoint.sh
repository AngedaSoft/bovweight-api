#!/usr/bin/env bash
set -e

cd /var/www/html

if [ ! -f .env ]; then
  echo "Creando .env desde .env.docker"
  cp .env.docker .env
fi

if [ -z "${APP_KEY:-}" ] && ! grep -q "^APP_KEY=base64:" .env; then
  echo "Generando APP_KEY"
  php artisan key:generate --force
fi

echo "Esperando MySQL..."
TIMEOUT=60
while ! mysqladmin ping -h"${DB_HOST:-mysql}" -P"${DB_PORT:-3306}" --silent; do
  TIMEOUT=$((TIMEOUT-1))
  if [ "$TIMEOUT" -le 0 ]; then
    echo "ERROR: timeout esperando MySQL" >&2
    exit 1
  fi
  sleep 1
done

echo "Corriendo migraciones..."
php artisan migrate --force --seed

php artisan storage:link || true
php artisan config:cache
php artisan route:cache

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
