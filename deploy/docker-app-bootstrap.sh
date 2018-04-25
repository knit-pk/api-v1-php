#!/usr/bin/env sh

if [ "$APP_ENV" = '' ]; then
    echo "Could not determine APP_ENV"
    exit 1
fi
echo "APP_ENV=$APP_ENV"

if [ "$JWT_PASSPHRASE" = '' ]; then
    echo "Could not generate jwt keys"
    exit 1
fi

if [ "$APP_ENV" = 'prod' ] || [ "$APP_ENV" = 'stage' ]; then
    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/php-overrides.ini

    # Wait until database is ready
    if [ "$DOCKERIZE_WAIT_FOR" != '' ]; then
        if dockerize -wait $DOCKERIZE_WAIT_FOR -timeout 30s; then
            php bin/console doctrine:migrations:migrate -n
        else
            echo "Could not migrate database"
        fi
    fi
else
    composer install --prefer-dist --no-progress --no-suggest --no-interaction --ansi
    make fix-symfony-cache

    # Wait until database is ready
    if [ "$DOCKERIZE_WAIT_FOR" != '' ]; then
        if dockerize -wait ${DOCKERIZE_WAIT_FOR} -timeout 30s; then
            # Update/create database schema and seed with data
            make fixtures-reload
        else
            echo "Could not migrate data fixtures to database"
        fi
    fi
fi

make generate-jwt-keys
make cache-warmup-docker
