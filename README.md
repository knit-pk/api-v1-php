# KNIT Restful API
Development configuration of KNIT API written in PHP, deployed using docker.

- [Run project](#run-project)
- [Useful commands](#useful-commands)
- [Informations](#informations)
- [Postman Collection](#postman-collection)

## Run project
Requirements to work with project: `git`, `docker`, `docker-compose`, `composer`

### Installation

1. Clone repository
   ```bash
   $ git clone https://github.com/knit-pk/api-v1-php.git
   $ cd api-v1-php
   ```
2. Run project
   ```bash
   $ docker-compose up -d # Build and run docker containers
   ```
3. (Recommended) Verify that containers started without errors and check log files under `/logs`.

## Useful commands

- Stop project (does not destroy data)
    ```bash
    $ docker-compose stop
    ```

- Down project (destroys data)
    ```bash
    $ docker-compose down
    ```

- Rebuild docker images
    ```bash
    $ docker-compose build
    $ docker-compose up -d 
    ```

## Informations
Docker images:
- PHP (php:7.1-fpm-alpine)
- Nginx (nginx:alpine)
- Mysql (mysql:5.7)
- PhpMyAdmin (phpmyadmin/phpmyadmin:latest)

PHP Stack:
- Symfony (Flex)
- Api Platform

## Postman Collection
TODO