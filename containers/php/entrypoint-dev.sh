#!/usr/bin/env sh

set -e

if [ -f '.env' ]; then
    . .env
fi

if [ "$1" = "php-fpm" ]; then
    composer install
    php artisan migrate --force
elif [ "$1" = "scheduler" ]; then
    set -- crond -f
elif [ "$1" = "worker" ]; then
    set -- php artisan queue:work --tries=3 --timeout=1800
fi

exec "$@"
