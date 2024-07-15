#!/usr/bin/env sh

set -e

if [ -f '.env' ]; then
    . .env
fi

if [ "$APP_ENV" != "local" ]; then
    php artisan optimize
fi

if [ "$1" = "php-fpm" ]; then
    composer install
    php artisan migrate --force
    if [ "$(id -u)" = '0' ]; then
        chown -R www-data:www-data storage
    fi
elif [ "$1" = "scheduler" ]; then
    wait-for "${PHP_HOST:?Missing PHP_HOST}:${PHP_PORT:?Missing PHP_PORT}" -t 60
    set -- crond -f -d 6
elif [ "$1" = "worker" ]; then
    wait-for "${PHP_HOST:?Missing PHP_HOST}:${PHP_PORT:?Missing PHP_PORT}" -t 60
    if [ "$(id -u)" = '0' ]; then
        set -- su -s /bin/sh -c "php artisan queue:work --tries=3 --timeout=1800" www-data
    else
        set -- php artisan queue:work --tries=3 --timeout=1800
    fi
fi

exec "$@"
