# KNIT Restful API
![travis](https://api.travis-ci.org/knit-pk/api-v1-php.svg?branch=develop)

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

- Feed database with default data.

  Remarks: Please verify whether php container name is set accordingly via `docker ps` command.
    ```bash
    $ docker exec -it api_php_1 bin/console doctrine:fixtures:load -n
    ```

## Informations
Docker images:
- PHP (php:7.1-fpm-alpine)
- Nginx (nginx:alpine)
- Varnish - Http Cache (alpine:3.6)
- MySQL (mysql:5.7)

PHP Stack:
- Symfony (v3.3.*)
- Api Platform (v2.1.*)

## Postman Collection
Always for newest version:
https://www.getpostman.com/collections/07d9bde930835627078a