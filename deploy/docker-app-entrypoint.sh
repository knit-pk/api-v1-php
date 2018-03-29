#!/usr/bin/env sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- bin/server "$@"
fi

if [ "$1" = 'bin/server' ] || [ "$1" = 'bin/console' ]; then
	docker-app-bootstrap
fi

exec docker-php-entrypoint "$@"
