# KNIT Restful API
![travis](https://api.travis-ci.org/knit-pk/api-v1-php.svg?branch=develop)
[![Dependency Status](https://www.versioneye.com/user/projects/5a887caa0fb24f6da09a6179/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/5a887caa0fb24f6da09a6179)

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
   $ git clone https://github.com/knit-pk/api-v1-php.git api
   $ cd api
   ```
2. Run project
   ```bash
   $ docker-compose up -d # Build and run docker containers
   ```
3. (Recommended) Verify that application started without errors: `/logs/supervisor/docker-app-startup.log`.

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

- Follow log output from containers
    ```bash
    $ docker-compose logs --follow
    ```

Remarks: Please verify whether php container name is set accordingly in following commands via `docker ps` command.
If repository was cloned as instructed to `api` directory, container name: `api_backend_1` should be proper.

- Feed database with default data.

    ```bash
    $ docker exec -it api_backend_1 make fixtures-reload
    ```

- Clean and rebuild application cache.

    ```bash
    $ docker exec -it api_backend_1 make cache-warmup-docker
    ```

## Informations
Docker images:
- PHP (php:7.2-fpm-alpine3.7)
- Nginx (nginx:alpine)
- Varnish - Http Cache (alpine:3.7)
- MySQL (mysql:5.7)

PHP Stack:
- Symfony (v4.0.*)
- Api Platform (v2.1.*)

## Postman Collection
Always for newest version:
https://www.getpostman.com/collections/07d9bde930835627078a
