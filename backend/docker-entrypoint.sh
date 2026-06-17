#!/bin/sh

set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install --no-interaction --prefer-dist --optimize-autoloader

php artisan key:generate --force

echo "Waiting for MySQL..."

until php artisan migrate --force; do
    sleep 5
done

exec "$@"
