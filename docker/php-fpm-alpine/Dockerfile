FROM php:7.1-fpm-alpine

ARG TIMEZONE="Europe/Warsaw"
ENV TZ=${TIMEZONE}

# Install custom packages
RUN apk update && apk upgrade && \
    apk add --update --no-cache tzdata zip supervisor make

# Install xdebug via pecl
RUN docker-php-source extract && \
    apk add --no-cache --virtual .phpize-deps-configure $PHPIZE_DEPS && \
    pecl install xdebug && \
    pecl install apcu && \
    docker-php-ext-enable xdebug apcu && \
    apk del .phpize-deps-configure && \
    docker-php-source delete

# Install php extensions available by docker-php-ext-install command
RUN docker-php-ext-install pdo_mysql opcache

# Configure xdebug
RUN echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini  && \
    echo "xdebug.remote_autostart=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_connect_back=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone && \
    printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini && \
    "date"

# Remove unneded packages
RUN apk del tzdata

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Move startup.sh for local scripts run one time
COPY app_startup.sh /usr/local/bin

# Supervisor configuration
COPY supervisord.conf /etc/supervisord.conf

# Make structure for logs
RUN mkdir -p /var/www/app/var/logs && \
    touch /var/www/app/var/logs/startup.log
RUN mkdir -p /var/log/supervisor && \
    touch /var/log/supervisor/supervisor.log
RUN mkdir -p /var/log/php && \
    touch /var/log/php/fpm.log 

# Run supervisor
ENTRYPOINT ["supervisord", "-c", "/etc/supervisord.conf"]

WORKDIR /var/www/app