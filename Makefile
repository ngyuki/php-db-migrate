PHP_VERSION := \
	7.0 \
	7.1 \
	7.2 \
	7.3 \
	7.4

DOCKER_USER := $(shell id -u):$(shell id -g)

all: dev

.PHONY: dev
dev:
	DOCKER_USER=${DOCKER_USER} docker-compose run --rm php sh

.PHONY: composer
composer:
	DOCKER_USER=${DOCKER_USER} docker-compose run --rm php composer install -o --ansi --prefer-dist

.PHONY: qa
qa:
	DOCKER_USER=${DOCKER_USER} docker-compose run --rm php composer qa

.PHONY: build
build:
	DOCKER_USER=${DOCKER_USER} docker-compose run --rm --no-deps php composer build

.PHONY: down
down:
	DOCKER_USER=${DOCKER_USER} docker-compose down -v

.PHONY: tests
tests: ${PHP_VERSION:%=tests/%}

.PHONY: ${PHP_VERSION:%=tests/%}
${PHP_VERSION:%=tests/%}: tests/%:
	rm -f composer.lock
	PHP_VERSION=${@F} DOCKER_USER=${DOCKER_USER} docker-compose run --rm php php -v
	PHP_VERSION=${@F} DOCKER_USER=${DOCKER_USER} docker-compose run --rm php composer install -o --ansi --prefer-dist
	PHP_VERSION=${@F} DOCKER_USER=${DOCKER_USER} docker-compose run --rm php composer test
