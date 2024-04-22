#!/usr/bin/env sh

set -e

if [ -f '.env' ]; then
    . .env
fi

if [ "$1" = 'supervisord' ]; then
    if [ "$APP_ENV" = "local" ]; then
        su -s '/bin/sh' www-data -c "composer install"
        su -s '/bin/sh' www-data -c "php artisan migrate"
    else
        php artisan optimize
        php artisan migrate --force
        chown -R www-data:www-data storage
    fi
fi

exec "$@"
