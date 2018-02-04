FROM composer:1.6 as composer
FROM php:7.2-fpm-alpine3.7 as php

ARG TIMEZONE="Europe/Warsaw"
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV TZ=${TIMEZONE}
ENV DOT_ENV=.env.mysql
ENV DOCKERIZE_VERSION=v0.6.0 \
    DOCKERIZE_WAIT_FOR=tcp://mysql:3306

# Install custom packages
RUN apk update && apk upgrade && \
    apk add --no-cache tzdata zip supervisor make openssl

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

# Install Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --classmap-authoritative

# Copy phpini
COPY php.ini /usr/local/etc/php/php.ini

# Copy script that runs on container startup
COPY startup.sh /usr/local/bin/docker-app-startup

# Supervisor configuration
COPY supervisord.conf /etc/supervisord.conf

# Make structure with proper permissions for application and logs
RUN mkdir -p /var/www/app/var && \
    chown www-data:www-data /var/www/app/var && \
    chmod 777 /var/www/app/var
RUN mkdir -p /var/log/supervisor && \
    touch /var/log/supervisor/supervisor.log && \
    touch /var/log/supervisor/docker-app-startup.log
RUN mkdir -p /var/log/php && \
    touch /var/log/php/fpm.log 

# Run supervisor
ENTRYPOINT ["supervisord", "-c", "/etc/supervisord.conf"]

WORKDIR /var/www/app