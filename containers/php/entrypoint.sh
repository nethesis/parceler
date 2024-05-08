#!/usr/bin/env sh

set -e

if [ -f '.env' ]; then
    . .env
fi

php artisan optimize

if [ "$1" = "php-fpm" ]; then
    # migrate database
    php artisan migrate --force
elif [ "$1" = "scheduler" ]; then
    # remove scheduler command, replace the $@
    set -- crond -f
elif [ "$1" = "worker" ]; then
    # remove worker command, replace the $@
    set -- su -s /bin/sh -c "php artisan queue:work --tries=3 --timeout=1800" www-data
fi

exec "$@"
