# Test container: the unit suite and the 100% coverage gate on the PHP version given by PHP_VERSION.
# The EspoCRM checkout (site/) is built by .github/scripts/prepare-site.sh when the container starts.
ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli

# git and unzip are pinned to the Debian 13 (trixie) release the php:8.2 to php:8.5 cli images share.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git=1:2.47.* unzip=6.0-29* \
    && rm -rf /var/lib/apt/lists/* \
    && pecl install pcov \
    && docker-php-ext-enable pcov

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN useradd --create-home --uid 10001 app \
    && mkdir /app \
    && chown app:app /app

# The home of an arbitrary uid is not writable, so composer keeps its cache in /tmp.
ENV COMPOSER_HOME=/tmp/composer

WORKDIR /app

USER 10001

# One-shot test container: the check confirms the tools the gate needs are in the image.
HEALTHCHECK --interval=30s --timeout=10s --retries=3 \
    CMD ["sh", "-c", "php -m | grep -qx pcov && git --version > /dev/null && unzip -v > /dev/null && composer --version > /dev/null"]
