#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then

	if [ "$APP_ENV" != 'prod' ]; then
	    cp $DOT_ENV .env
	    cat .env | grep APP_ENV

		composer install --prefer-dist --no-progress --no-suggest --no-interaction
		make fix-symfony-cache

		# Wait until database is ready
        if dockerize -wait ${DOCKERIZE_WAIT_FOR} -timeout 30s; then
            # Update/create database schema and seed with data
            make fixtures-reload
		else
            echo "Could not migrate data fixtures to database"
        fi
	else
	    composer auto-scripts
	    # Wait until database is ready
        if dockerize -wait ${DOCKERIZE_WAIT_FOR} -timeout 30s; then
	        php bin/console doctrine:migrations:migrate
	    else
            echo "Could not migrate database"
        fi
	fi

	make generate-jwt-keys
	make cache-warmup-docker
fi

exec docker-php-entrypoint "$@"