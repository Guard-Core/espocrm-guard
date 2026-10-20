# AGENTS.md
Guidance for AI agents (including Claude Code) working in this repository.

## Project Overview

Guard-Core/espocrm-guard (https://github.com/Guard-Core/espocrm-guard) is an EspoCRM extension, "Guard Core for EspoCRM". It registers the psr15-guard middleware as a global API middleware (`globalMiddlewareClassNameList`) so the guard-core-php engine screens `public/api/v1/` and the portal API. It is off until `guardEnabled` is true, and the keys `guardEnabled` and `guard` are hidden from the Settings API (level `system`).

- The extension is distributed as a zip (`build/guard-core-<version>.zip`, built by `npm run extension`) that an administrator uploads under Administration > Extensions. It is not on Packagist or any registry. The module `composer.json` is named `rennf93/espocrm-guard-core` and only bundles `rennf93/psr15-guard` and, transitively, `rennf93/guard-core-php`.
- This repository contains NO security logic. Detection, rate limiting, bans, headers and responses live in guard-core-php. The module reads the `guard` config, merges it with a default policy, builds the engine objects and wires them to EspoCRM's logger and config.
- EspoCRM `>=9.0.0` (`extension.json` `acceptableVersions`) and PHP `>=8.2`. The version lives in `package.json` (and `package-lock.json`); `extension.json` carries none, and the zip name takes it from `package.json`.
- Releases are tag driven. `master` is protected: never push to it, never merge into it, never create tags or releases as part of agent work. Releases are immutable, so `release.yml` attaches the zip and its `.sha256` to a DRAFT release that the maintainer publishes.
- Docs site: MkDocs Material in `docs/`, versioned with mike (`.mike.yml`), deployed by `docs.yml`. `mkdocs.yml` sets `site_dir: docs_site` because `site/` is the EspoCRM checkout: a default `mkdocs build` would wipe it.

## Ecosystem Position

- `rennf93/guard-core-php` (https://github.com/Guard-Core/guard-core-php) is the engine. It owns `SecurityConfig`, `RouteConfig`, `GuardEngine`, `RedisHandler`, `BehaviorRule` and every verdict.
- `rennf93/psr15-guard` (https://github.com/Guard-Core/psr15-guard) is the PSR-15 adapter this module composes: `GuardMiddleware` takes the engine, PSR-17 factories, the route map and an optional agent handler. The module floors it at `^1.6.1`.
- This repository is the EspoCRM adapter layer: `Api\Middleware` is the EspoCRM middleware, `Api\GuardMiddlewareBuilder` wires logger, Redis, country database and agent, `Api\SecurityConfigBuilder` and `Api\RouteConfigBuilder` map the `guard` array onto `SecurityConfig` and `RouteConfig`, `Api\DefaultPolicy` is the policy applied when `guardEnabled` is true, `Api\GuardAgentFactory` creates the optional agent.
- `rennf93/guard-agent-php` is the optional telemetry agent. It is resolved by name and is not a dependency; the operator runs `composer require rennf93/guard-agent-php` in the EspoCRM root.
- `psr/http-*` and Slim come from EspoCRM itself. The module `composer.json` `replace`s the four `psr/http-*` packages so none ends up in the zip, and `GuardMiddlewareBuilder` uses EspoCRM's `Slim\Psr7` factories.

## Boundary Rules

The extension wires; the engine decides.

- MUST NOT implement detection rules, penetration signatures, rate limiting, IP banning, security headers or CORS. The extension only translates config into engine objects and copies nothing but the engine's verdicts.
- MUST stay opt-in: `Middleware::process()` returns `$handler->handle($request)` and builds nothing unless `guardEnabled` is true.
- MUST fail loudly on bad config: an unknown `guard` key, an unknown route option, an invalid behavior rule, an unsupported Redis URL or an `agent` map without the agent package throws instead of being ignored.
- MUST keep `guard` and `guardEnabled` out of every API response: `Resources/metadata/app/config.json` declares both at level `system`, and `src/scripts/AfterUninstall.php` removes both from the config files when the module is uninstalled.
- MUST NOT bundle `psr/*` or Slim in the zip. `.github/scripts/check-extension-zip.sh` checks the zip contents and size on every CI build and release.
- MUST NOT add GDPR-relevant data handling: the module logs engine verdicts (check name, reason, client IP, method, path, status) through EspoCRM's logger, and nothing else.

## Quick Start

1. `npm ci` and `composer install` (root dev tooling: PHPUnit and PHPStan).
2. `make prepare-test`: builds `site/`, a sparse checkout of espocrm/espocrm (application, custom, dev, bootstrap.php, composer files) with `composer install` in it and the module libraries installed under `site/custom/Espo/Modules/GuardCore/vendor`. `ESPOCRM_REF=9.0.0 make prepare-test` builds it from the oldest supported release. PHP 8.2 only works with EspoCRM 9.0.0 (EspoCRM 9 supports PHP 8.2 to 8.4, `stable` supports 8.3 to 8.5).
3. `make lint`, then `make local-test` (needs Redis on 127.0.0.1:6420, `REDIS_HOST` and `REDIS_PORT` override it) or `make test` (Docker with a private Redis).

## Development Commands

| Command | What it runs |
| --- | --- |
| `make install` | `composer install` and `npm ci` |
| `make prepare-test` | `.github/scripts/prepare-site.sh $(ESPOCRM_REF)` (default `stable`) |
| `make lint` | php -l sweep, `composer validate --strict` (root and module), PHPStan level 8 |
| `make local-test` | PHPStan, then `php .github/coverage-runner.php` (PHPUnit with a 100% line gate, pcov) |
| `make test` | the same in Docker (`compose.yml`, `PHP_VERSION`, `ESPOCRM_REF`) with Redis |
| `make extension` | `npm run extension`: builds `build/guard-core-<version>.zip` |
| `make example-zip` | builds the zip and copies it into `examples/*/guard-core.zip` (gitignored) |
| `make bump-version VERSION=x.y.z` | `.github/scripts/bump_version.py`: `package.json`, `package-lock.json`, the CHANGELOG scaffold |
| `.github/scripts/check-extension-zip.sh build/guard-core-<version>.zip` | zip contents and size budget |
| `.github/scripts/smoke-simple.sh` and `smoke-advanced.sh` | the live smoke assertions, against the running example stacks |
| `pre-commit run --all-files` | generic hooks, markdownlint, php -l, PHPStan |
| `mkdocs build --strict` | builds the docs into `docs_site/` |

## Project Structure

```text
src/files/custom/Espo/Modules/GuardCore/Api/    the module classes
src/files/custom/Espo/Modules/GuardCore/Resources/  module.json, autoload.json, metadata/app/{api,config}.json
src/files/custom/Espo/Modules/GuardCore/composer.json, composer.lock  module libraries (the lock is what ships)
src/scripts/AfterUninstall.php                  removes guardEnabled and guard on uninstall
src/.agents/skills/espocrm-guard/SKILL.md       agent skill (not part of the zip)
tests/unit/Espo/Modules/GuardCore/              PHPUnit suite (MiddlewareTest, GuardAgentFactoryTest, AfterUninstallTest)
php_scripts/, build.js, config-default.json     espo-extension-tools helpers and build
examples/simple_app, examples/advanced_app      Docker Compose stacks with a real EspoCRM and the built zip
docs/, mkdocs.yml, .mike.yml                    docs site
.github/workflows/                              ci, code-ql, static-analysis, semgrep, docksec, live-smoke, upstream-drift, scheduled-lint, release, docs, container-release, housekeeping
.github/scripts/                                bump_version.py, prepare-site.sh, check-extension-zip.sh, smoke-*.sh
.github/coverage-runner.php                     100% line coverage gate
```

## Technology Stack

- PHP `>=8.2`; EspoCRM `>=9.0.0`. CI matrix: EspoCRM 9.0.0 on PHP 8.2, 8.3, 8.4 and EspoCRM stable on PHP 8.3, 8.4, 8.5.
- Dev tooling (root `composer.json`): `phpunit/phpunit ^11.5 || ^12.5` (PHPUnit 12 needs PHP 8.3, so the root `composer.lock` is not tracked and the version resolves per PHP) and `phpstan/phpstan ^2.1` at level 8, scanning `site/`.
- Module libraries: `rennf93/psr15-guard ^1.6.1` (and guard-core-php through it).
- Extension tooling: `espo-extension-tools#0.4.5` (Node 22, npm).
- Actions are pinned by commit SHA with a version comment.
- Examples run the official `espocrm/espocrm` image (10.0.10-apache), MariaDB 11.4, Redis 7 and nginx 1.27.

## Testing Guidelines

- `vendor/bin/phpunit` runs the unit suite; `php .github/coverage-runner.php` adds the 100% line gate over `src/files/custom/Espo/Modules/GuardCore` and `src/scripts`.
- One test needs Redis on `REDIS_HOST:REDIS_PORT` (default 127.0.0.1:6420) and skips without it.
- `GuardAgentFactoryTest` loads a stub of the agent class, so its tests depend on the missing-package test running first.
- Any behavior change needs a test before the PR lands, and the module code must stay at 100% line coverage.

## Code Quality Standards

- PHPStan level 8 with zero errors, and no `@phpstan-ignore` to get there.
- 4-space indentation (`.editorconfig`), LF line endings (`.gitattributes`).
- A function name says what it does; one function does one thing.
- Conventional commits: `feat:`, `fix:`, `docs:`, `test:`, `ci:`, `chore:`; signed, no attribution trailers. Squash subjects end with `(#N)`.
- No em dashes or en dashes in any text; one paragraph per line in Markdown.

## Best Practices

- `master` is protected. Work on a branch, push, open a PR that closes an open issue. Never push to `master`, never tag, never publish a release as part of agent work.
- Never `git add` `vendor/`, `site/`, `build/`, `docs_site/`, `node_modules/`, `coverage.php` or `.DS_Store`. Stage explicit paths only.
- Never write or edit a `.env` file.
- Keep the module thin. If a change adds detection, verdict logic or response shaping, it belongs in guard-core-php or psr15-guard.
- Update README.md and `docs/` in the same PR whenever config keys or the default policy change.

## Related Projects

- `rennf93/guard-core-php`: https://github.com/Guard-Core/guard-core-php. The engine.
- `rennf93/psr15-guard`: https://github.com/Guard-Core/psr15-guard. The PSR-15 adapter this module composes.
- `rennf93/guard-agent-php`: https://github.com/Guard-Core/guard-agent-php. The optional telemetry agent.
- `rennf93/slim-guard`: https://github.com/Guard-Core/slim-guard. The Slim 4 adapter, a sibling of this module.
- `Guard-Core/espocrm-guard`: this repository, the EspoCRM layer of the guard-core ecosystem.
