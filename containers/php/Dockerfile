FROM docker.io/php:8.3.4-fpm-alpine as base
WORKDIR /var/www/html
# install software needed for all environments
RUN apk add --no-cache \
        rclone \
        sqlite
COPY --from=docker.io/composer:2.7.2 /usr/bin/composer /usr/local/bin/composer
COPY --from=docker.io/mlocati/php-extension-installer:2.2.5 /usr/bin/install-php-extensions /usr/local/sbin/install-php-extensions
RUN install-php-extensions pcntl
ADD --chmod=777 \
    --checksum=sha256:206a8f9b2177703fc5aa924d85ad6c72e82413e2d09635b4c9c82a1b65b5b3d5 \
    https://github.com/eficode/wait-for/releases/download/v2.2.4/wait-for /usr/local/bin/wait-for
# configure entrypoints and image
RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/www-data \
    && echo "access.log = /dev/null" >> /usr/local/etc/php-fpm.d/zz-docker.conf
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

FROM base as development
RUN apk add --no-cache \
        bash \
        git \
        shadow \
        sudo \
    && install-php-extensions xdebug \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && echo "ALL ALL=(ALL:ALL) NOPASSWD: ALL" > "/etc/sudoers.d/password_less_sudo"
COPY --chmod=777 containers/php/entrypoint-dev.sh /usr/local/sbin/entrypoint
ARG UID
ARG GID
RUN usermod -u "$UID" www-data \
    && groupmod -g "$GID" www-data
ENV XDEBUG_MODE=develop,debug,coverage
ENV XDEBUG_CONFIG="client_host=host.docker.internal"
USER www-data

FROM base as production
RUN install-php-extensions opcache \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --chmod=777 containers/php/entrypoint.sh /usr/local/sbin/entrypoint
COPY containers/php/config/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
ARG COMPOSER_ALLOW_SUPERUSER=1
COPY composer.json .
COPY composer.lock .
RUN composer install --no-dev --no-scripts --no-cache
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY --chown=www-data:www-data storage ./storage
COPY artisan .
RUN composer dump-autoload --optimize \
    && echo -n "opcache.max_accelerated_files=" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo $(find . -name "*.php" | wc -l | awk '{print (int($1/1000)+2)*1000}') >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && rm /usr/local/bin/composer \
    && rm /usr/local/sbin/install-php-extensions
VOLUME ["/var/www/html/storage"]
