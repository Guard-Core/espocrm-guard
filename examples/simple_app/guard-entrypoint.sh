#!/bin/bash
# Wraps the EspoCRM image entrypoint: once EspoCRM has installed itself, installs the built extension zip through
# EspoCRM's own extension command and applies the guard config. /tmp/guard-ready marks the end, the health check reads it.
set -euo pipefail

if [[ "${1-}" == apache2* ]]; then
  (
    until grep -qs "'isInstalled' => true" /var/www/html/data/config-internal.php; do
      sleep 3
    done

    if runuser -u www-data -- /opt/guard/guard-setup.sh; then
      touch /tmp/guard-ready
    else
      echo "error: the guard setup failed" >&2
    fi
  ) &
fi

exec docker-entrypoint.sh "$@"
