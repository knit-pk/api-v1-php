#!/usr/bin/env sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- bin/console swoole:server:run "$@"
fi

if [ "$1" = 'bin/console' ]; then
	docker-app-bootstrap
fi

exec docker-php-entrypoint "$@"
