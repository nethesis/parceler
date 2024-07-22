#!/usr/bin/env sh

set -e

if [ -f '.env' ]; then
    . .env
fi

if [ "$1" = "php-fpm" ]; then
    if [ "$APP_ENV" != "local" ]; then
        php artisan optimize
    else
        composer install
    fi
    php artisan migrate --force
    if [ "$(id -u)" = '0' ]; then
        chown -R www-data:www-data storage
    fi
elif [ "$1" = "scheduler" ]; then
    wait-for "${PHP_HOST:?Missing PHP_HOST}:${PHP_PORT:?Missing PHP_PORT}" -t 60
    if [ "$APP_ENV" != "local" ]; then
        php artisan optimize
    fi
    set -- crond -f -d
elif [ "$1" = "worker" ]; then
    wait-for "${PHP_HOST:?Missing PHP_HOST}:${PHP_PORT:?Missing PHP_PORT}" -t 60
    if [ "$APP_ENV" != "local" ]; then
        php artisan optimize
    fi
    set -- su -s /bin/sh -c "php artisan queue:work --tries=3 --timeout=1800" www-data
fi

exec "$@"
