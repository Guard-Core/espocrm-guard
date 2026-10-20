#!/usr/bin/env bash
# Assertions for examples/simple_app: the guard is on with the default policy (passive mode).
# Usage: .github/scripts/smoke-simple.sh [project]   (the stack must be up)
set -euo pipefail
# shellcheck source=smoke-lib.sh source-path=SCRIPTDIR
. "$(dirname "$0")/smoke-lib.sh"

project="${1:-espocrm-guard-simple}"
base="http://localhost:8080"
root="$(cd "$(dirname "$0")/../.." && pwd)"
compose=(-p "$project" -f "$root/examples/simple_app/docker-compose.yml")

expect "GET /api/v1/App/user answers" 200 -u "$SMOKE_USER:$SMOKE_PASSWORD" "$base/api/v1/App/user"

# Passive mode: the probe is detected but not blocked.
expect "SQL injection probe passes in passive mode" 200 -u "$SMOKE_USER:$SMOKE_PASSWORD" \
  -G "$base/api/v1/Account" --data-urlencode "where=1' OR '1'='1' UNION SELECT password FROM user--"

if log_contains "${compose[@]}" -- "Guard would block by"; then
  echo "ok: the passive log line 'Guard would block by' is in data/logs"
else
  echo "FAIL: no 'Guard would block by' line in data/logs"
  exit 1
fi

settings_hide_guard "$base"
echo "simple_app smoke: all assertions passed"
