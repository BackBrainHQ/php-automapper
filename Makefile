.DEFAULT: _info
	@echo "No make target defined"

.PHONY: _info
_info:
	@echo "Running make target '$(MAKECMDGOALS)' of '$(abspath $(lastword $(MAKEFILE_LIST)))'"

.PHONY: vendors
vendors: _info
	composer install

.PHONY: vendors-update
vendors-update: _info
	composer update

# Run linters
.PHONY: lint
lint: _info _lint-php

.PHONY: _lint-php
_lint-php:
	php vendor/bin/php-cs-fixer fix --dry-run --diff

.PHONY: lint-fix
lint-fix: _info
	php vendor/bin/php-cs-fixer fix --diff

.PHONY: test
test: _info _test-stan _test-unit-low-deps _test-unit-high-deps

.PHONY: _test-stan
_test-stan:
	vendor/bin/phpstan --memory-limit=512M analyse

.PHONY: _test-unit-high-deps
_test-unit-high-deps:
	composer update
	XDEBUG_MODE=coverage vendor/bin/phpunit -d memory_limit=256M --coverage-cobertura=./cobertura.xml --coverage-text

.PHONY: _test-unit-low-deps
_test-unit-low-deps:
	composer update --prefer-lowest
	XDEBUG_MODE=coverage vendor/bin/phpunit -d memory_limit=256M --coverage-cobertura=./cobertura.xml --coverage-text
