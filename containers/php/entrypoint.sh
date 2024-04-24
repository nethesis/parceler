#!/usr/bin/env sh

set -e

if [ -f '.env' ]; then
    . .env
fi

if [ "$1" = 'php-fpm' ]; then
    if [ "$APP_ENV" = "local" ]; then
        composer install
    else
        php artisan optimize
        chown -R www-data:www-data storage
    fi
    php artisan migrate --force
elif [ "$1" = 'crond' ] || [ "$3" = 'queue:work' ]; then
    wait-for "${PHP_HOST:?Missing PHP_HOST}:${PHP_PORT:?Missing PHP_PORT}" -t 60
fi

exec "$@"
