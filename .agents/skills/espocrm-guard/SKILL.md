---
name: espocrm-guard
description: Use when an agent works in Guard-Core/espocrm-guard or touches its EspoCRM extension surface: the opt-in Guard Core API middleware that wires psr15-guard and guard-core-php into the EspoCRM API and contains no security logic of its own; covers the guard and guardEnabled config keys (hidden from the Settings API), the default policy (passive mode, 401 ban rule, lead capture and password rate limits), Redis and trusted proxy settings, the optional guard-agent-php agent, the extension zip build (npm run extension), the make targets, the sparse EspoCRM test site (make prepare-test) and the PHPUnit 100% line coverage gate.
---

# espocrm-guard

## Quick Reference

- EspoCRM extension "Guard Core", module `GuardCore`, namespace `Espo\Modules\GuardCore\Api`. EspoCRM `>=9.0.0`, PHP `>=8.2`.
- Registers `Espo\Modules\GuardCore\Api\Middleware` as a global API middleware. It does nothing until `guardCore.enabled` is true in `data/config-internal.php`.
- `guardCore` is the settings map (`enabled` plus every setting): every engine option is a `SecurityConfig` argument, every route option a `RouteConfig` argument. Unknown keys throw. Closures and objects cannot be configured.
- `guardCore`, `guard` and `guardEnabled` are level `system` in `Resources/metadata/app/config.json`, so the Settings API never returns them. `src/scripts/AfterUninstall.php` removes them on uninstall. `guardEnabled` and `guard` are the deprecated 1.0.0 keys: they still work with a warning, and `src/scripts/AfterInstall.php` moves them under `guardCore` on upgrade. Both at once while enabled is an error.
- The extension holds no security logic. Every verdict comes from guard-core-php through psr15-guard.
- Commands: `make prepare-test`, `make lint`, `make local-test` (Redis on 127.0.0.1:6420), `make test` (Docker), `make extension`, `make bump-version VERSION=x.y.z`.

## Default policy

With only `guardCore.enabled` set: passive mode, attack screening on, no body scan on `POST /api/v1/Attachment/chunk/`, a ban after more than 10 responses with status 401 in 5 minutes on `GET /api/v1/App/user` and `/api/v1/`, rate limits on `POST /api/v1/LeadCapture/` (10 per minute) and the two password routes (5 per 5 minutes). Operator keys override key by key, a `null` route entry switches a default route off.

## Gotchas

- Redis is off by default, so rate limits and bans do not hold across PHP-FPM requests until `enableRedis` and `redisUrl` (`redis://host[:port]` only) are set.
- The client is `REMOTE_ADDR`. Behind a proxy set `guard.trustedProxies`; `ipAddressServerParam` is not read.
- `site/` is the EspoCRM checkout PHPStan and PHPUnit use. Never run a docs build into it: `mkdocs.yml` sets `site_dir: docs_site`.
- PHP 8.2 works only with EspoCRM 9.0.0. The root `composer.lock` is resolved for PHP 8.2 (`config.platform`), so every PHP runs PHPUnit 11.5.
- Releases are immutable: a `v*` tag makes `release.yml` draft a release with the zip and its `.sha256`; the maintainer publishes it.
