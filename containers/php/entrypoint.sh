#!/usr/bin/env sh

set -e

if [ -f '.env' ]; then
    . .env
fi

# Bootstrap application
if [ "$1" = 'php-fpm' ]; then
    if [ "$APP_ENV" = "local" ]; then
        composer i
    elif [ "$APP_ENV" = "production" ]; then
        php artisan optimize
    fi
    php artisan migrate --force
    php artisan storage:link
    chown -R www-data:www-data storage
fi

exec "$@"
