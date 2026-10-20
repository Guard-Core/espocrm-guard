#!/usr/bin/env bash
# Helpers for the live smoke scripts: curl assertions against the example stacks. Source it, do not run it.
set -euo pipefail

SMOKE_USER="${SMOKE_USER:-admin}"
SMOKE_PASSWORD="${SMOKE_PASSWORD:-example-admin-password}"
SMOKE_BODY="$(mktemp)"
trap 'rm -f "$SMOKE_BODY"' EXIT

# status <curl args...>: prints the HTTP status, keeps the body in $SMOKE_BODY.
status() {
  curl -sS --max-time 20 -o "$SMOKE_BODY" -w '%{http_code}' "$@"
}

# expect <name> <expected status> <curl args...>
expect() {
  local name="$1" expected="$2" code
  shift 2
  code="$(status "$@")" || { echo "FAIL: $name (curl error)"; exit 1; }
  if [ "$code" != "$expected" ]; then
    echo "FAIL: $name (expected $expected, got $code)"
    echo "body: $(head -c 300 "$SMOKE_BODY")"
    exit 1
  fi
  echo "ok: $name -> $code"
}

# settings_hide_guard <base url>: GET /api/v1/Settings must not carry guard or guardEnabled, for the admin.
settings_hide_guard() {
  expect "GET /api/v1/Settings answers" 200 -u "$SMOKE_USER:$SMOKE_PASSWORD" "$1/api/v1/Settings"
  if grep -Eq '"guard(Enabled)?"' "$SMOKE_BODY"; then
    echo "FAIL: the Settings API exposes guard or guardEnabled"
    exit 1
  fi
  echo "ok: guard and guardEnabled are absent from GET /api/v1/Settings"
}

# log_contains <compose args...> -- <pattern>: greps EspoCRM's log files in the espocrm service.
log_contains() {
  local pattern="${*: -1}"
  local -a compose=("${@:1:$#-2}")
  docker compose "${compose[@]}" exec -T espocrm sh -c 'cat data/logs/*.log 2>/dev/null' | grep -q "$pattern"
}
