#!/usr/bin/env sh

set -e

if [ -f '.env' ]; then
    . .env
fi

if [ "$1" = "php-fpm" ]; then
    composer install
    php artisan migrate --force
elif [ "$1" = "scheduler" ]; then
    wait-for "${PHP_HOST:?Missing PHP_HOST}:${PHP_PORT:?Missing PHP_PORT}" -t 60
    set -- crond -f
elif [ "$1" = "worker" ]; then
    wait-for "${PHP_HOST:?Missing PHP_HOST}:${PHP_PORT:?Missing PHP_PORT}" -t 60
    set -- php artisan queue:listen --tries=3 --timeout=1800
fi

exec "$@"
