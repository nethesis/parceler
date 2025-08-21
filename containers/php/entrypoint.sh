#!/usr/bin/env sh

set -e

wait_for_services() {
    wait-for "${PHP_HOST:?Missing PHP_HOST}:${PHP_PORT:?Missing PHP_PORT}" -t 60
}

optimize() {
    if [ "$APP_ENV" != "local" ]; then
        php artisan optimize
    fi
}

if [ "$1" = "php-fpm" ]; then
    optimize
    if [ "$APP_ENV" = "local" ]; then
        composer install
    fi
    php artisan migrate --force
    if [ "$(id -u)" = '0' ]; then
        chown -R www-data:www-data storage
    fi
    exec "$@"
elif [ "$1" = "scheduler" ]; then
    wait_for_services
    optimize
    exec su -s /bin/sh -c "php artisan schedule:work --quiet" www-data
elif [ "$1" = "worker" ]; then
    wait_for_services
    optimize
    exec su -s /bin/sh -c "php artisan queue:work --tries=3 --timeout=1800" www-data
elif [ "$1" = "nightwatch" ]; then
    wait_for_services
    optimize
    exec su -s /bin/sh -c "php artisan nightwatch:agent --listen-on 0.0.0.0:2407" www-data
else
    exec "$@"
fi
