#!/usr/bin/env bash
# Checks the extension zip: the two bundled libraries are there as sources only, nothing EspoCRM already provides ships
# (psr/*, Slim), no tests or docs travel inside, and the size stays under a budget.
#
# Usage: .github/scripts/check-extension-zip.sh build/guard-core-<version>.zip
set -euo pipefail

zip="${1:?usage: check-extension-zip.sh <zip>}"
max_bytes=$((1024 * 1024))
vendor="files/custom/Espo/Modules/GuardCore/vendor"

listing="$(unzip -Z1 "$zip")"
fail=0

require() {
  if ! grep -q "$1" <<< "$listing"; then
    echo "FAIL: missing $2"
    fail=1
  fi
}

forbid() {
  if grep -Eqi "$1" <<< "$listing"; then
    echo "FAIL: $2 found:"
    grep -Ei "$1" <<< "$listing" | head -5
    fail=1
  fi
}

require "^${vendor}/rennf93/psr15-guard/src/GuardMiddleware.php$" "rennf93/psr15-guard sources"
require "^${vendor}/rennf93/guard-core-php/src/Engine/GuardEngine.php$" "rennf93/guard-core-php sources"
require "^manifest.json$" "manifest.json"
require "^scripts/AfterUninstall.php$" "scripts/AfterUninstall.php"
require "^files/custom/Espo/Modules/GuardCore/Api/Middleware.php$" "the module middleware"

forbid "^${vendor}/psr/" "psr/* packages (EspoCRM provides them)"
forbid "slim" "Slim files (EspoCRM provides it)"
forbid "^${vendor}/rennf93/[^/]+/(tests|docs|examples|conformance|\.github)/" "tests, docs or examples of a bundled library"
forbid "(^|/)(tests?|docs)/" "a tests or docs directory"

size="$(wc -c < "$zip" | tr -d ' ')"
if [ "$size" -gt "$max_bytes" ]; then
  echo "FAIL: $zip is $size bytes, over the budget of $max_bytes"
  fail=1
fi

if [ "$fail" -ne 0 ]; then
  exit 1
fi

echo "zip ok: $(basename "$zip"), $size bytes (budget $max_bytes), $(wc -l <<< "$listing" | tr -d ' ') entries"
