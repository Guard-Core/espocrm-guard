# advanced_app

A real EspoCRM (the official `espocrm/espocrm` image) behind nginx, with MariaDB and Redis, and the Guard Core extension installed from the zip this repository builds. Passive mode is off and the route policies are custom (`guard-config.php`):

- Redis holds the rate limit, behavior and ban state, so it holds across requests.
- nginx is the reverse proxy and `trustedProxies` lists its network (`172.29.60.0/24`, fixed in `docker-compose.yml`), so the client is resolved from `X-Forwarded-For`. nginx sets that header from `$remote_addr` instead of appending to what the client sent.
- `POST /api/v1/LeadCapture/` allows 3 requests per minute.
- `GET /api/v1/App/user` bans a client after 3 responses with status 401 in 5 minutes.

The agent section is documented in `guard-config.php` and in `docs/telemetry-agent.md`: it needs `composer require rennf93/guard-agent-php` in the EspoCRM root and an API key, so it stays commented out.

## Run

From the repository root:

```bash
make example-zip
docker compose -p espocrm-guard-advanced -f examples/advanced_app/docker-compose.yml up --build -d --wait
```

The first start takes a few minutes. nginx answers on `http://localhost:8081`, user `admin`, password `example-admin-password` (an example only). EspoCRM itself is not published.

## Try it

```bash
# The fourth lead capture request in a minute is rate limited
for i in 1 2 3 4; do
  curl -s -o /dev/null -w '%{http_code}\n' -X POST -H 'Content-Type: application/json' \
    -d '{"lastName":"Try"}' http://localhost:8081/api/v1/LeadCapture/some-key
done

# An attack probe is blocked (400) and logged as "Guard blocked by ..."
curl -u admin:example-admin-password -G http://localhost:8081/api/v1/Account \
  --data-urlencode "where=1' OR '1'='1' UNION SELECT password FROM user--"

# Repeated wrong passwords ban the client, valid credentials included (403)
for i in 1 2 3 4; do
  curl -s -o /dev/null -w '%{http_code}\n' -u admin:wrong http://localhost:8081/api/v1/App/user
done
curl -s -o /dev/null -w '%{http_code}\n' -u admin:example-admin-password http://localhost:8081/api/v1/App/user
```

`.github/scripts/smoke-advanced.sh` runs these assertions in order (the ban last), and the Live Smoke workflow runs it on every pull request. The `container-release.yml` workflow publishes this stack's EspoCRM image as `ghcr.io/guard-core/espocrm-guard-example`.

## Stop

```bash
docker compose -p espocrm-guard-advanced -f examples/advanced_app/docker-compose.yml down -v
```
