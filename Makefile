# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.
app_name=$(notdir $(CURDIR))
build_tools_directory=$(CURDIR)/build/tools
composer=$(shell which composer 2> /dev/null)

all: dev-setup lint build-js-production test

build: install-composer-deps build-js

# Dev env management
dev-setup: clean clean-dev install-composer-deps-dev js-init

composer.phar:
	curl -sS https://getcomposer.org/installer | php

install-composer-deps: composer.phar
	php composer.phar install --no-dev -o

install-composer-deps-dev: composer.phar
	php composer.phar install -o

js-init:
	yarn install

yarn-update:
	yarn update

# Building
build-js: js-init
	yarn run dev

build-js-production: js-init
	yarn run build

watch-js: js-init
	yarn run watch

# Linting
lint: js-init
	yarn run lint

lint-fix: js-init
	yarn run lint:fix

# Style linting
stylelint: js-init
	yarn run stylelint

stylelint-fix: js-init
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
	git checkout composer.json
	git checkout composer.lock
	rm -rf vendor

pack: install-composer-deps
	mkdir -p archive
	tar --exclude='./Makefile' --exclude='./webpack*' --exclude='./.*' --exclude='./ts' --exclude='./tests' --exclude='./node_modules' --exclude='./archive' -zcvf ./archive/cloud_bbb.tar.gz . --transform s/^./bbb/

# Tests
test:
	./vendor/phpunit/phpunit/phpunit -c phpunit.xml
	./vendor/phpunit/phpunit/phpunit -c phpunit.integration.xml
