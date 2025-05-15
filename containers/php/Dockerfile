ARG PHP_EXTENSIONS="pcntl"
ARG ADDITIONAL_PACKAGES="bash rclone sqlite"

FROM php:8.4.7-fpm-alpine AS base
WORKDIR /var/www/html
# install software needed for all environments
COPY --from=docker.io/composer:2.8.9 /usr/bin/composer /usr/local/bin/composer
COPY --from=docker.io/mlocati/php-extension-installer:2.7.34 /usr/bin/install-php-extensions /usr/local/sbin/install-php-extensions
ADD --chmod=777 \
    --checksum=sha256:206a8f9b2177703fc5aa924d85ad6c72e82413e2d09635b4c9c82a1b65b5b3d5 \
    https://github.com/eficode/wait-for/releases/download/v2.2.4/wait-for /usr/local/bin/wait-for
# configure entrypoints and image
RUN echo "* * * * * cd /var/www/html && php artisan schedule:run" > /etc/crontabs/www-data
COPY --chmod=777 containers/php/entrypoint.sh /usr/local/sbin/entrypoint
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
ARG PHP_EXTENSIONS
ARG ADDITIONAL_PACKAGES
RUN apk add --no-cache ${ADDITIONAL_PACKAGES} \
    && install-php-extensions ${PHP_EXTENSIONS}


FROM base AS development
RUN apk add --no-cache \
        git \
        shadow \
        sudo \
    && install-php-extensions xdebug \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && echo "ALL ALL=(ALL:ALL) NOPASSWD: ALL" > "/etc/sudoers.d/password_less_sudo"
ARG UID
ARG GID
RUN usermod -u "$UID" www-data \
    && groupmod -g "$GID" www-data
ENV XDEBUG_MODE=develop,debug,coverage,profile
ENV XDEBUG_CONFIG="client_host=host.docker.internal"
USER www-data

FROM base AS vendor
COPY composer.json .
COPY composer.lock .
RUN composer install --no-dev --no-scripts --no-cache --ignore-platform-reqs

FROM vendor AS vendor_dev
RUN composer install --no-scripts --no-cache --ignore-platform-reqs

FROM base AS production
RUN install-php-extensions opcache \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY containers/php/config/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
ARG COMPOSER_ALLOW_SUPERUSER=1
COPY --from=vendor /var/www/html/composer.json .
COPY --from=vendor /var/www/html/composer.lock .
COPY --from=vendor /var/www/html/vendor vendor
COPY app app
COPY bootstrap bootstrap
COPY config config
COPY database database
COPY public public
COPY resources resources
COPY routes routes
COPY --chown=www-data:www-data storage storage
COPY artisan .
RUN composer check-platform-reqs \
    && composer dump-autoload --optimize \
    && echo -n "opcache.max_accelerated_files=" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo $(find . -name "*.php" | wc -l | awk '{print (int($1/1000)+2)*1000}') >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && rm /usr/local/bin/composer \
    && rm /usr/local/sbin/install-php-extensions
VOLUME ["/var/www/html/storage"]

FROM base AS testing
RUN install-php-extensions xdebug
COPY --from=vendor_dev /var/www/html/composer.json .
COPY --from=vendor_dev /var/www/html/composer.lock .
COPY --from=vendor_dev /var/www/html/vendor vendor
COPY app app
COPY bootstrap bootstrap
COPY config config
COPY database database
COPY public public
COPY resources resources
COPY routes routes
COPY --chown=www-data:www-data storage storage
COPY tests tests
COPY artisan .
COPY phpunit.xml .
COPY .env.example .env.testing
RUN composer check-platform-reqs \
    && composer dump-autoload --optimize \
    && php artisan key:generate --env=testing
USER www-data
