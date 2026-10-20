# simple_app

A real EspoCRM (the official `espocrm/espocrm` image) with MariaDB, and the Guard Core extension installed from the zip this repository builds. `guardEnabled` is true and nothing else is set, so the guard runs the default policy in passive mode: attacks are logged, nothing is blocked.

The official image is used because it installs EspoCRM on first start from environment variables, and its `custom/`, `data/` and `client/custom/` volumes are what an extension install needs. The extension is installed through EspoCRM's own `command.php extension` once the installer has finished (`guard-entrypoint.sh`, `guard-setup.sh`), and the keys in `guard-config.php` are written to `data/config.php`.

## Run

From the repository root:

```bash
make example-zip
docker compose -p espocrm-guard-simple -f examples/simple_app/docker-compose.yml up --build -d --wait
```

The first start takes a few minutes: EspoCRM installs itself, then the extension. EspoCRM answers on `http://localhost:8080`, user `admin`, password `example-admin-password` (an example only).

## Try it

```bash
# A normal request
curl -u admin:example-admin-password http://localhost:8080/api/v1/App/user

# An attack probe: it passes in passive mode, and the guard logs what it would have blocked
curl -u admin:example-admin-password -G http://localhost:8080/api/v1/Account \
  --data-urlencode "where=1' OR '1'='1' UNION SELECT password FROM user--"
docker compose -p espocrm-guard-simple -f examples/simple_app/docker-compose.yml exec espocrm \
  sh -c 'grep "Guard would block by" data/logs/*.log'

# The guard keys are not in the Settings API
curl -u admin:example-admin-password http://localhost:8080/api/v1/Settings | grep -c '"guard'
```

`.github/scripts/smoke-simple.sh` runs these assertions, and the Live Smoke workflow runs it on every pull request.

## Stop

```bash
docker compose -p espocrm-guard-simple -f examples/simple_app/docker-compose.yml down -v
```
