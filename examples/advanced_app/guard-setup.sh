#!/bin/bash
# Runs as www-data inside the EspoCRM container, after EspoCRM is installed.
set -euo pipefail

cd /var/www/html

if [ ! -d custom/Espo/Modules/GuardCore ]; then
  bin/command extension --file=/opt/guard/guard-core.zip
fi

php /opt/guard/apply-config.php /opt/guard/guard-config.php
bin/command clear-cache
