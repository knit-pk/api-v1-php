ARG COMPOSER_BASE_TAG=1.6
ARG PHP_BASE_TAG=7.2-cli-alpine3.7

FROM composer:$COMPOSER_BASE_TAG as composer
FROM php:$PHP_BASE_TAG

ARG TIMEZONE="Europe/Warsaw"
ARG DOCKERIZE_VERSION=v0.6.0
ARG APCU_VERSION=5.1.11
ARG SWOOLE_VERSION=2.1.1

ENV TZ ${TIMEZONE}

# Install custom packages
RUN apk update && apk upgrade && \
    apk add --no-cache tzdata zip make openssl linux-headers

# Install dockerize
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz

# Install xdebug via pecl
RUN docker-php-source extract && \
    apk add --no-cache --virtual .phpize-deps-configure $PHPIZE_DEPS && \
    pecl install apcu-$APCU_VERSION && \
    pecl install swoole-$SWOOLE_VERSION && \
    docker-php-ext-enable apcu swoole && \
    apk del .phpize-deps-configure && \
    docker-php-source delete

# Install php extensions available by docker-php-ext-install command
RUN docker-php-ext-install pdo_mysql opcache

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TIMEZONE /etc/localtime && echo $TIMEZONE > /etc/timezone && \
    "date" && \
    apk del tzdata

# Prepare run scripts
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY deploy/php-overrides.ini /usr/local/etc/php/conf.d/
COPY deploy/docker-app-entrypoint.sh /usr/local/bin/docker-app-entrypoint
COPY deploy/docker-app-bootstrap.sh /usr/local/bin/docker-app-bootstrap
RUN chmod +x /usr/local/bin/docker-app-entrypoint && \
    chmod +x /usr/local/bin/docker-app-bootstrap

WORKDIR /usr/src/api
ENTRYPOINT ["docker-app-entrypoint"]
CMD ["bin/server"]

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --classmap-authoritative

# Prevent the reinstallation of vendors at every changes in the source code
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress --no-suggest

COPY . /usr/src/api

RUN mkdir -p var/cache var/log var/sessions public/media/upload && \
    composer dump-autoload --apcu --no-dev && \
    bin/docker-console assets:install public && \
    chmod -R 777 var public

# Environment variables used by application bootstrap
ENV DOCKERIZE_WAIT_FOR '' \
    JWT_PRIVATE_KEY_PATH 'config/jwt/private.pem' \
    JWT_PUBLIC_KEY_PATH 'config/jwt/public.pem'