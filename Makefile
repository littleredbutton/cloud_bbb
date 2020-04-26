# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.
app_name=$(notdir $(CURDIR))
build_tools_directory=$(CURDIR)/build/tools
composer=$(shell which composer 2> /dev/null)

all: dev-setup lint build-js-production test

build: install-composer-deps build-js

# Dev env management
dev-setup: clean clean-dev install-composer-deps-dev yarn-init

composer.phar:
	curl -sS https://getcomposer.org/installer | php

install-composer-deps: composer.phar
	php composer.phar install --no-dev -o

install-composer-deps-dev: composer.phar
	php composer.phar install -o

yarn-init:
	yarn install

yarn-update:
	yarn update

# Building
build-js:
	yarn run dev

build-js-production:
	yarn run build

watch-js:
	yarn run watch

# Linting
lint:
	yarn run lint

lint-fix:
	yarn run lint:fix

# Style linting
stylelint:
	yarn run stylelint

stylelint-fix:
	yarn run stylelint:fix

phplint:
	./vendor/bin/php-cs-fixer fix --dry-run

phplint-fix:
	./vendor/bin/php-cs-fixer fix

# Cleaning
clean:
	rm -rf js/*

clean-dev:
	rm -rf node_modules

# Tests
test:
	./vendor/phpunit/phpunit/phpunit -c phpunit.xml
	./vendor/phpunit/phpunit/phpunit -c phpunit.integration.xml
