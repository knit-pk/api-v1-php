#!/usr/bin/env sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- bin/console swoole:server:run "$@"
fi

if [ "$1" = 'bin/console' ]; then
    echo "NGINX_PORT=$NGINX_PORT"
    sed -i "s#NGINX_PORT#$NGINX_PORT#g" /etc/nginx/conf.d/default.conf
    nginx
	docker-app-bootstrap
fi

exec docker-php-entrypoint "$@"
