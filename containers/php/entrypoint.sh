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
    fi
    php artisan migrate --force
    chown -R www-data:www-data storage
elif [ "$1" = 'crond' ] || [ "$3" = 'queue:work' ] || [ "$3" = 'queue:listen' ]; then
    wait-for "${PHP_HOST:?Missing PHP_HOST}:${PHP_PORT:?Missing PHP_PORT}" -t 60
fi

exec "$@"
