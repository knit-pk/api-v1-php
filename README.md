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
   docker-compose up -d # Build and run docker containers
   ```
3. (Recommended) Prepare enviroment
   ```bash
   chmod +x start.sh # Make start.sh executable
   bash start.sh     # Run script
   ```
4. (Recommended) Create database schema and load fixtures
    ```bash
    bin/console doctrine:schema:create    # Create database schema
    bin/console doctrine:fixtures:load -n # Seed database with generated data
    ```

## Useful commands

- Stop project (doesnt destroy data)
    ```bash
    docker-compose stop
    ```

- Rebuild images
    ```bash
    docker-compose build
    docker-compose up -d 
    ```

## Informations
Used docker images:
- PHP 7.1 FastCGI
- Nginx
- Mysql 5.7
- ELK 1.x (Elasticsearch, Logstash, Kibana)
- PhpMyAdmin

Main (php):
- Symfony (Flex)
- Api Platform

## Postman Collection
TODO