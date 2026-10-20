Release Notes
=============

___

v1.0.0 (2026-10-20)
-------------------

The first release: opt-in application-layer security for the EspoCRM API as an extension (v1.0.0)
-------------------------------------------------------------------------------------------------

### Added

- **The opt-in Guard Core API middleware as an EspoCRM extension**, packaged from espocrm/espocrm#3836. One global API middleware (`globalMiddlewareClassNameList`) runs the psr15-guard middleware and the guard-core-php engine on `public/api/v1/` and the portal API. It does nothing, and builds nothing, until `guardEnabled` is true.
- **Config keys `guardEnabled` and `guard`**, hidden from the Settings API through the module's `app/config.json` (level `system`). Every engine option is a key of `guard` and every route option a key of a route. An unknown key or an invalid route option throws instead of being ignored.
- **A default policy**: passive mode, attack screening, ban rules on repeated 401, rate limits on lead capture and on the password routes. Settings you give override it key by key, and a `null` route entry switches a default route off.
- **Optional Redis, trusted proxies and the telemetry agent** (`rennf93/guard-agent-php`, loaded only when installed).
- **Examples**: `examples/simple_app` (default policy, passive mode) and `examples/advanced_app` (Redis, nginx with `trustedProxies`, passive mode off, custom route policies), each a Docker Compose stack with a real EspoCRM and MariaDB that installs the built zip through EspoCRM's extension install.
- **Documentation** at guard-core.github.io/espocrm-guard: installation, configuration, usage and the default policy, the telemetry agent, what it covers and does not.
- **The uninstall removes `guardEnabled` and `guard` from the config files**, because once the extension is gone nothing would hide the Redis URL and the agent API key from the Settings API.

### Test

- The unit suite covers every line of the module code, `AfterUninstall` included, and CI fails below 100% line coverage. CI runs PHP 8.2 to 8.5 against EspoCRM 9.0.0 and the stable release, builds the zip and checks its contents and size, and runs both examples against a live EspoCRM.

### Chore

- Baseline tooling in line with the rest of the Guard Core repositories: workflows pinned by commit SHA, CodeQL, PHPStan level 8, Semgrep, upstream drift checks against psr15-guard, guard-core-php and EspoCRM master, pre-commit, a Makefile, versioned documentation, and the community and governance files.
- **The release is an immutable GitHub release with the zip attached.** Pushing a `v*` tag builds the zip and its `.sha256` file and creates a draft release with the CHANGELOG section as its body.

___
