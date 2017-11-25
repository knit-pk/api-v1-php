#!/usr/bin/env sh

# Run local scripts
chmod 777 /var/app/log
cd /var/www/app

# Copy default dotenv file
cp ${DOT_ENV} .env

# Generate jwt keys using paraphrase from .env
make generate-jwt-keys

# Install vendor packages, and publish public/bundles
composer install -n -o --no-progress --no-ansi --no-suggest
make fix-easy-admin-cache

# Wait until database is ready
if dockerize -wait ${DOCKERIZE_WAIT_FOR} -timeout 30s; then
    # Update/create database schema and seed with data
    bin/console doctrine:migrations:migrate first --no-interaction
    bin/console doctrine:migrations:migrate --no-interaction
    bin/console doctrine:fixtures:load -n
else
    echo "Could not migrate data to database"
fi