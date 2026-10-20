# Test container: the unit suite and the 100% coverage gate on the PHP version given by PHP_VERSION.
# The EspoCRM checkout (site/) is built by .github/scripts/prepare-site.sh when the container starts.
ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/* \
    && pecl install pcov \
    && docker-php-ext-enable pcov

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
