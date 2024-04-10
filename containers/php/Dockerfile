FROM php:8.3.4-fpm-alpine as base
WORKDIR /var/www/html
COPY --from=mlocati/php-extension-installer:2.2.5 /usr/bin/install-php-extensions /usr/local/sbin/
COPY --from=composer:2.7.2 /usr/bin/composer /usr/local/bin/composer

FROM base as development
# install additional packages and xdebug
RUN apk add --no-cache \
        bash \
        git \
        shadow \
        sudo \
    && install-php-extensions \
        xdebug \
    # configuring php and passwordless sudo
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && echo "ALL ALL=(ALL:ALL) NOPASSWD: ALL" > "/etc/sudoers.d/password_less_sudo"
COPY --chmod=777 containers/php/entrypoint.sh /usr/local/sbin/entrypoint
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
ARG UID
ARG GID
RUN usermod -u "$UID" www-data \
    && groupmod -g "$GID" www-data
ENV XDEBUG_MODE=develop,debug,coverage
ENV XDEBUG_CONFIG="client_host=host.docker.internal"
USER www-data
