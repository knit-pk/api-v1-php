<p align="center">
  <img width="300" height="300" src="https://secure.gravatar.com/avatar/02af0b4745bcd6c6997955997639cb65.jpg?s=300&r=g&d=mm">
</p>

# KNIT API (RESTful + GraphQL)
![travis](https://api.travis-ci.org/knit-pk/api-v1-php.svg?branch=develop)
[![Maintainability](https://api.codeclimate.com/v1/badges/685724aefa95446cbbc8/maintainability)](https://codeclimate.com/github/knit-pk/api-v1-php/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/685724aefa95446cbbc8/test_coverage)](https://codeclimate.com/github/knit-pk/api-v1-php/test_coverage)
[![Slack Status](https://knitwebdevpk.herokuapp.com/badge.svg)](https://knitwebdevpk.herokuapp.com)
[![Open Source Love](https://badges.frapsoft.com/os/v1/open-source.svg?v=103)](https://github.com/ellerbrock/open-source-badges/)
[![MIT Licence](https://badges.frapsoft.com/os/mit/mit.svg?v=103)](https://opensource.org/licenses/mit-license.php)



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

- Stop project
    ```bash
    $ docker-compose stop
    ```

- Stop project with destroying all its data
    ```bash
    $ docker-compose down -v
    ```

- Rebuild and run docker images
    ```bash
    $ docker-compose build --pull
    $ docker-compose up -d
    ```

- Follow log output from containers
    ```bash
    $ docker-compose logs --follow
    ```

- Feed database with default data.

    ```bash
    $ docker-compose exec api make fixtures-reload
    ```

- Clean and rebuild application cache.

    ```bash
    $ docker-compose exec api make cache-warmup-docker
    ```

- Running feature tests

    ```bash
    $ docker-compose exec api composer behat
    ```

- Run swoole sever locally

    ```bash
    $ bin/console swoole:server:run
    # or 
    $ bin/console s:s:r
    ```

## Informations
Docker images:
- PHP (php:7.2-cli-alpine3.7)
- Nginx (nginx:4-alpine)
- Redis (redis:4-alpine)
- Varnish - Http Cache (alpine:3.7)
- MySQL (mysql:5.7)

PHP Stack:
- Symfony (v4.1.*)
- Swoole Extension (v4.*)
- API Platform (v2.3.*)

## Postman Collection
Always for newest version:
https://www.getpostman.com/collections/07d9bde930835627078a
