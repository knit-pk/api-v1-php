FROM composer:1.6 as composer
FROM php:7.2-fpm-alpine3.7 as php

ARG DOT_ENV=none
ARG TIMEZONE="Europe/Warsaw"
ARG DOCKERIZE_VERSION=v0.6.0
ARG DOCKERIZE_WAIT_FOR=tcp://mysql:3306

ENV TZ ${TIMEZONE}
ENV DOT_ENV ${DOT_ENV}
ENV DOCKERIZE_VERSION ${DOCKERIZE_VERSION}
ENV DOCKERIZE_WAIT_FOR ${DOCKERIZE_WAIT_FOR}

# Install custom packages
RUN apk update && apk upgrade && \
    apk add --no-cache tzdata zip make openssl

# Install dockerize
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz

# Install xdebug via pecl
RUN docker-php-source extract && \
    apk add --no-cache --virtual .phpize-deps-configure $PHPIZE_DEPS && \
    pecl install apcu && \
    docker-php-ext-enable apcu && \
    apk del .phpize-deps-configure && \
    docker-php-source delete

# Install php extensions available by docker-php-ext-install command
RUN docker-php-ext-install pdo_mysql opcache

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone && \
    printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini && \
    "date"

# Remove unneded packages
RUN apk del tzdata

# Prepare run scripts
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY deploy/php.ini /usr/local/etc/php/php.ini
COPY deploy/docker-app-entrypoint.sh /usr/local/bin/docker-app-entrypoint
RUN chmod +x /usr/local/bin/docker-app-entrypoint

WORKDIR /usr/src/api
ENTRYPOINT ["docker-app-entrypoint"]
CMD ["php-fpm"]

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --classmap-authoritative

# Prevent the reinstallation of vendors at every changes in the source code
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress --no-suggest

COPY . /usr/src/api

RUN mkdir -p var/cache var/logs var/sessions \
	&& composer dump-autoload --classmap-authoritative --no-dev \
	&& chown -R www-data:www-data var
