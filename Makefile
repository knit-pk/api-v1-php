ifndef APP_ENV
	include .env
endif
ifndef JWT_PRIVATE_KEY_PATH
    JWT_PRIVATE_KEY_PATH=config/jwt/private.pem
endif
ifndef JWT_PUBLIC_KEY_PATH
    JWT_PUBLIC_KEY_PATH=config/jwt/public.pem
endif

CURR_USER := $(shell whoami)

fix-symfony-cache:
	@if [ "$(shell whoami)" = 'root' ]; then\
		echo "## Fixing symfony cache ##";\
		chmod 777 -R var;\
	else\
		echo "## Non-root user ##";\
	fi
.PHONY: fix-symfony-cache

cache-warmup-docker: cache-warmup
	@${MAKE} fix-symfony-cache
.PHONY: cache-warmup-docker

###> symfony/framework-bundle ###
CONSOLE := $(shell which bin/console)
sf_console:
ifndef CONSOLE
	@printf "Run `composer require cli` to install the Symfony console.\n"
endif

cache-clear:
ifdef CONSOLE
	@$(CONSOLE) cache:clear --no-warmup
else
	@rm -rf var/cache/*
endif
.PHONY: cache-clear

cache-warmup: cache-clear
ifdef CONSOLE
	@$(CONSOLE) cache:warmup
else
	@printf "Cannot warm up the cache (needs symfony/console)\n"
endif
.PHONY: cache-warmup

serve_as_sf: sf_console
ifndef CONSOLE
	@${MAKE} serve_as_php
endif
	@$(CONSOLE) | grep server:start > /dev/null || ${MAKE} serve_as_php
	@$(CONSOLE) server:start

	@printf "Quit the server with `bin/console server:stop`\n"

serve_as_php:
	@printf "Server listening on http://127.0.0.1:8000\n"
	@printf "Quit the server with CTRL-C.\n"
	@printf "Run `composer require symfony/web-server-bundle` for a better web server\n"
	php -S 127.0.0.1:8000 -t public

serve:
	@${MAKE} serve_as_sf
.PHONY: sf_console serve serve_as_sf serve_as_php
###< symfony/framework-bundle ###

###> lexik/jwt-authentication-bundle ###
OPENSSL_BIN := $(shell which openssl)
generate-jwt-keys:
ifndef OPENSSL_BIN
	$(error "Unable to generate keys (needs OpenSSL)")
endif
	mkdir -p config/jwt
	openssl genrsa -passout pass:${JWT_PASSPHRASE} -out ${JWT_PRIVATE_KEY_PATH} -aes256 4096
	openssl rsa -passin pass:${JWT_PASSPHRASE} -pubout -in ${JWT_PRIVATE_KEY_PATH} -out ${JWT_PUBLIC_KEY_PATH}
	@echo "RSA key pair successfully generated"
###< lexik/jwt-authentication-bundle ###

fixtures-reload:
ifdef CONSOLE
	@$(CONSOLE) doctrine:schema:drop --force
	@$(CONSOLE) doctrine:schema:create
	@$(CONSOLE) doctrine:fixtures:load -vvv -n
else
	@printf "Cannot reload fixtures (needs symfony/console)\n"
endif
.PHONY: cache-warmup