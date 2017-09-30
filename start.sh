#!/usr/bin/env bash

if [ -d var ]; 
then echo 'Removing /var..' && rm -rf var; 
fi;

cp .env.dist .env
mkdir var
chmod 777 var
make generate-jwt-keys
chmod 777 -R config/jwt
composer install -n
chmod 777 -R var