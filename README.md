# KNIT Restful API
![travis](https://api.travis-ci.org/knit-pk/api-v1-php.svg?branch=develop)
[![Dependency Status](https://www.versioneye.com/user/projects/5a99ba910fb24f2cfe29ccd2/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/5a99ba910fb24f2cfe29ccd2)

Development configuration of KNIT API written in PHP, deployed using docker.

- [Run project](#run-project)
- [Useful commands](#useful-commands)
- [Informations](#informations)
- [Postman Collection](#postman-collection)

## Run project
Requirements to work with project: `git`, `docker`, `docker-compose`

### Installation

1. Clone repository
   ```bash
   $ git clone https://github.com/knit-pk/api-v1-php.git knit-api
   ```
2. Run project
   ```bash
   $ cd knit-api
   $ docker-compose up
   ```

## Useful commands

- Stop project (does not destroy data)
    ```bash
    $ docker-compose stop
    ```

- Down project (destroys data)
    ```bash
    $ docker-compose down
    ```

- Rebuild and run docker images
    ```bash
    $ docker-compose up -d --build
    ```

- Follow log output from containers
    ```bash
    $ docker-compose logs --follow
    ```

- Feed database with default data.

    ```bash
    $ docker-compose exec backend make fixtures-reload
    ```

- Clean and rebuild application cache.

    ```bash
    $ docker-compose exec backend make cache-warmup-docker
    ```

- Running feature tests

    ```bash
    $ docker-compose exec backend composer test-features
    ```

## Informations
Docker images:
- PHP (php:7.2-fpm-alpine3.7)
- Nginx (nginx:4-alpine)
- Varnish - Http Cache (alpine:3.7)
- MySQL (mysql:5.7)

PHP Stack:
- Symfony (v4.0.*)
- Api Platform (v2.2.*)

## Postman Collection
Always for newest version:
https://www.getpostman.com/collections/07d9bde930835627078a
