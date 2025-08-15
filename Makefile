.DEFAULT: _info
	@echo "No make target defined"

.PHONY: _info
_info:
	@echo "Running make target '$(MAKECMDGOALS)' of '$(abspath $(lastword $(MAKEFILE_LIST)))'"

.PHONY: docs
docs: _info
	$(MAKE) -C ./docs/site build

.PHONY: clean
clean: _info
	rm -rf vendor .phpunit.cache .php-cs-fixer.cache cobertura.xml composer.lock

.PHONY: vendors
vendors: _info
	composer install

.PHONY: high-deps
high-deps: _info
	composer update

.PHONY: low-deps
low-deps: _info
	composer update --prefer-lowest

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
test: _info _test-stan _test-unit

.PHONY: _test-unit
_test-unit: _test-unit-low-deps _test-unit-high-deps

.PHONY: _test-stan
_test-stan: _test-stan-low-deps _test-stan-high-deps

.PHONY: _test-stan-high-deps
_test-stan-high-deps: high-deps
	vendor/bin/phpstan --memory-limit=512M analyse

.PHONY: _test-stan-low-deps
_test-stan-low-deps: low-deps
	vendor/bin/phpstan --memory-limit=512M analyse

.PHONY: _test-unit-high-deps
_test-unit-high-deps: high-deps
	XDEBUG_MODE=coverage vendor/bin/phpunit -d memory_limit=256M --coverage-cobertura=./cobertura.xml --coverage-text

.PHONY: _test-unit-low-deps
_test-unit-low-deps: low-deps
	XDEBUG_MODE=coverage vendor/bin/phpunit -d memory_limit=256M --coverage-cobertura=./cobertura.xml --coverage-text


