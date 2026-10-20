# espocrm-guard Makefile, harmonized with the guard-core PHP family and the Python baselines.
# `make test` runs in Docker with a private Redis, like the Python repos. Every other target uses the local php,
# composer and node, and falls back to Docker (php:8.3-cli, composer:2) when php or composer is missing.

PHP_IMAGE ?= php:8.3-cli
COMPOSER_IMAGE ?= composer:2
PHP_VERSION ?= 8.3
ESPOCRM_REF ?= stable
MODULE = src/files/custom/Espo/Modules/GuardCore

.PHONY: install test local-test lint prepare-test extension example-zip bump-version clean help

help:
	@echo "install        composer and npm dependencies"
	@echo "prepare-test   build site/, the sparse EspoCRM checkout (ESPOCRM_REF=stable or 9.0.0)"
	@echo "test           PHPStan and the unit suite with the 100% line coverage gate, in Docker with Redis"
	@echo "local-test     the same on the local PHP, Redis on 127.0.0.1:6420 (REDIS_HOST, REDIS_PORT)"
	@echo "lint           php -l sweep, composer validate --strict, PHPStan level 8"
	@echo "extension      build build/guard-core-<version>.zip"
	@echo "example-zip    build the extension and copy it into examples/*/ for the example stacks"
	@echo "bump-version   VERSION=x.y.z, updates package.json, package-lock.json and the CHANGELOG scaffold"
	@echo "clean          remove vendor, site, build and caches"

install:
	@if command -v composer > /dev/null 2>&1; then \
		composer install --no-interaction --no-progress; \
	else \
		echo "composer not found locally, using $(COMPOSER_IMAGE)"; \
		docker run --rm -v "$$PWD":/app -w /app $(COMPOSER_IMAGE) composer install --no-interaction --no-progress; \
	fi
	npm ci

prepare-test:
	@.github/scripts/prepare-site.sh $(ESPOCRM_REF)

test:
	PHP_VERSION=$(PHP_VERSION) ESPOCRM_REF=$(ESPOCRM_REF) docker compose run --rm --build test
	docker compose down -v --remove-orphans

local-test:
	@test -d site || { echo "site/ is missing: run make prepare-test first"; exit 1; }
	vendor/bin/phpstan --memory-limit=1024M --no-progress
	php .github/coverage-runner.php

lint:
	@if command -v php > /dev/null 2>&1; then \
		php_lint="php -l"; \
		composer_cmd="composer"; \
	else \
		echo "php not found locally, using $(PHP_IMAGE)"; \
		php_lint="docker run --rm -v \"$$PWD\":/app -w /app $(PHP_IMAGE) php -l"; \
		composer_cmd="docker run --rm -v \"$$PWD\":/app -w /app $(COMPOSER_IMAGE) composer"; \
	fi; \
	status=0; \
	for f in $$(find src tests php_scripts .github bootstrap.php -name '*.php'); do \
		$$php_lint "$$f" > /dev/null || status=1; \
	done; \
	if [ $$status -eq 0 ]; then echo "LINT_OK"; else echo "LINT_FAILED"; fi; \
	$$composer_cmd validate --strict || status=1; \
	$$composer_cmd validate --strict --working-dir=$(MODULE) || status=1; \
	if [ $$status -eq 0 ]; then \
		test -d site || { echo "site/ is missing: run make prepare-test first"; exit 1; }; \
		vendor/bin/phpstan --memory-limit=1024M --no-progress || status=1; \
	fi; \
	exit $$status

extension:
	npm run extension

example-zip: extension
	cp build/guard-core-*.zip examples/simple_app/guard-core.zip
	cp build/guard-core-*.zip examples/advanced_app/guard-core.zip

bump-version:
	@if [ -z "$(VERSION)" ]; then \
		echo "Usage: make bump-version VERSION=x.y.z"; \
		exit 1; \
	fi; \
	python3 .github/scripts/bump_version.py $(VERSION)

clean:
	@rm -rf vendor site docs_site build node_modules coverage.php .phpunit.cache 2>/dev/null; \
	find . -name '*.cache' -not -path './.git/*' -delete 2>/dev/null; \
	echo "cleaned vendor/, site/, build/, node_modules/ and cache files"
