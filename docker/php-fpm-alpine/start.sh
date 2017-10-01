#!/usr/bin/env sh

# Local scripts
cp .env.dist .env
make generate-jwt-keys
composer install -n
chown www-data:www-data -R var
bin/console doctrine:schema:drop --force
bin/console doctrine:schema:create
bin/console doctrine:fixtures:load -n