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
   git clone https://github.com/knit-pk/api-v1-php.git
   cd api-v1-php
   ```
2. Run project
   ```bash
   docker-compose -p api up -d # Build and run docker containers
   ```
3. (Recommended) Prepare enviroment
   ```
   docker exec -it api_php_1 sh -c "start.sh"
   ```

## Useful commands

- Stop project (does not destroy data)
    ```bash
    docker-compose -p api stop
    ```

- Down project (destroys data)
    ```bash
    docker-compose -p api down
    ```

- Rebuild images
    ```bash
    docker-compose -p api build
    docker-compose -p api up -d 
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