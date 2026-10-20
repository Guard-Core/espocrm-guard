#!/usr/bin/env bash
# Assertions for examples/advanced_app: Redis, nginx with trusted proxies, passive mode off, custom route policies.
# Usage: .github/scripts/smoke-advanced.sh [project]   (the stack must be up)
# The 401 ban runs last because it bans the client address of this script.
set -euo pipefail
# shellcheck source=smoke-lib.sh source-path=SCRIPTDIR
. "$(dirname "$0")/smoke-lib.sh"

project="${1:-espocrm-guard-advanced}"
base="http://localhost:8081"
root="$(cd "$(dirname "$0")/../.." && pwd)"
compose=(-p "$project" -f "$root/examples/advanced_app/docker-compose.yml")
json='Content-Type: application/json'

expect "GET /api/v1/App/user answers through nginx" 200 -u "$SMOKE_USER:$SMOKE_PASSWORD" "$base/api/v1/App/user"

# Rate limit on the lead capture route: 3 per minute, the 4th is refused.
for i in 1 2 3; do
  code="$(status -X POST -H "$json" -d '{"lastName":"Smoke"}' "$base/api/v1/LeadCapture/smoke-key")"
  if [ "$code" = "429" ]; then
    echo "FAIL: lead capture request $i was rate limited too early"
    exit 1
  fi
  echo "ok: lead capture request $i -> $code (not rate limited)"
done
expect "lead capture request 4 is rate limited" 429 -X POST -H "$json" -d '{"lastName":"Smoke"}' "$base/api/v1/LeadCapture/smoke-key"

# Passive mode is off: the probe is blocked and the request never reaches EspoCRM.
expect "SQL injection probe is blocked" 400 -u "$SMOKE_USER:$SMOKE_PASSWORD" \
  -G "$base/api/v1/Account" --data-urlencode "where=1' OR '1'='1' UNION SELECT password FROM user--"
expect "XSS body is blocked" 400 -u "$SMOKE_USER:$SMOKE_PASSWORD" -X POST -H "$json" \
  -d '{"name":"smoke","description":"<script>alert(1)</script>"}' "$base/api/v1/Account"

if log_contains "${compose[@]}" -- "Guard blocked by"; then
  echo "ok: the log line 'Guard blocked by' is in data/logs"
else
  echo "FAIL: no 'Guard blocked by' line in data/logs"
  exit 1
fi

settings_hide_guard "$base"

# Ban rule: the 4th response with status 401 on App/user bans the client, valid credentials included.
for i in 1 2 3; do
  expect "wrong password $i -> 401" 401 -u "$SMOKE_USER:wrong-password" "$base/api/v1/App/user"
done
expect "wrong password 4 -> 401 (the ban rule fires on this response)" 401 -u "$SMOKE_USER:wrong-password" "$base/api/v1/App/user"
expect "the banned client is refused with valid credentials" 403 -u "$SMOKE_USER:$SMOKE_PASSWORD" "$base/api/v1/App/user"

echo "advanced_app smoke: all assertions passed"
