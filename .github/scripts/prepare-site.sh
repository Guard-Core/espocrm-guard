#!/usr/bin/env bash
# Builds site/, the sparse EspoCRM checkout that unit tests and PHPStan run against, then installs the module libraries
# into it.
#
# Usage: .github/scripts/prepare-site.sh [espocrm-ref]
#   espocrm-ref: a tag or branch of espocrm/espocrm, default "stable" (the oldest supported release is 9.0.0).
set -euo pipefail

ref="${1:-stable}"
root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
module="custom/Espo/Modules/GuardCore"

# Composer downloads dist archives from the GitHub API, which now and then answers 5xx.
retry() {
  local attempt
  for attempt in 1 2 3; do
    "$@" && return 0
    echo "attempt $attempt failed: $*" >&2
    sleep $((attempt * 5))
  done
  return 1
}

cd "$root"
rm -rf site
mkdir site

git -C site init --quiet
git -C site remote add origin https://github.com/espocrm/espocrm.git
git -C site sparse-checkout set --no-cone /application /custom /dev /bootstrap.php /composer.json /composer.lock
git -C site fetch --quiet --depth 1 origin "$ref"
git -C site checkout --quiet FETCH_HEAD

# Only the autoloader is used, so the platform extensions EspoCRM needs at runtime are not required here.
retry composer install --no-interaction --no-progress --quiet --ignore-platform-reqs --working-dir=site

mkdir -p "site/$module"
cp "src/files/$module/composer.json" "src/files/$module/composer.lock" "site/$module/"
retry composer install --no-interaction --no-progress --quiet --working-dir="site/$module"

echo "site ready: espocrm/espocrm@$ref"
