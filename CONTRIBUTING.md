# Contributing to Guard Core for EspoCRM

Thanks for considering a contribution to Guard Core for EspoCRM, part of the Guard Core ecosystem (this repo follows the conventions of the Python baseline, guard-core and fastapi-guard, and of the PHP adapters, psr15-guard and slim-guard).

## Development Setup

Requirements:

- PHP 8.2 or later (CI runs 8.2, 8.3, 8.4 and 8.5, see the matrix in `.github/workflows/ci.yml`)
- Composer 2
- Node 22 and npm, for the extension build
- Docker, for `make test`, the examples and `pre-commit` hosts without a PHP toolchain

```bash
npm ci
composer install
make prepare-test      # builds site/, a sparse EspoCRM checkout (ESPOCRM_REF=9.0.0 for the oldest supported release)
make lint              # php -l, composer validate --strict, PHPStan level 8
make local-test        # PHPStan, then PHPUnit with the 100% line coverage gate (Redis on 127.0.0.1:6420)
make test              # the same in Docker with a private Redis
make extension         # builds build/guard-core-<version>.zip
```

One unit test needs Redis on `REDIS_HOST:REDIS_PORT` (default 127.0.0.1:6420) and skips without it. Both lock files are tracked: the root `composer.lock` is resolved for PHP 8.2 (`config.platform`), so every supported PHP runs the same PHPUnit 11.5, and the module's `composer.lock` decides what the zip ships.

## Quality Gates

Run before pushing (CI enforces the same checks):

- `make lint`
- `make local-test` (or `make test`): the unit suite with 100% line coverage of the module code, `AfterUninstall` included
- `pre-commit run --all-files`
- `composer audit`, at the root and in `src/files/custom/Espo/Modules/GuardCore`
- If you touch the examples, the Live Smoke workflow shows how they run: `make example-zip`, then `docker compose -f examples/<app>/docker-compose.yml up --build -d --wait` and `.github/scripts/smoke-simple.sh` or `smoke-advanced.sh`

This extension holds no security logic. Detection, rate limiting and bans belong in guard-core-php, and the PSR-7 translation in psr15-guard. A change that adds detection here belongs in the engine instead.

## Pull Requests

- Every PR closes an open issue ("Delivers issue: #N") or carries the `no-issue` label (chores and dependency bumps).
- Keep the CI green; one clean push per PR is preferred.
- Commit messages: conventional style (`fix(scope): ...`, `feat(scope): ...`, `ci(scope): ...`), signed, no attribution trailers.
- Do not edit version strings by hand: `make bump-version VERSION=x.y.z` updates `package.json`, `package-lock.json` and the CHANGELOG scaffold.

## Security

Never open public issues for security vulnerabilities. Follow SECURITY.md and report via GitHub security advisories.

## Questions

Open a GitHub Discussion in this repository or ask in the Guard Discord (#help).
