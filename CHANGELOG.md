Release Notes
=============

___

v1.1.0 (2026-10-31)
-------------------

One extension-owned config key, `guardCore`, with an automatic migration (v1.1.0)
---------------------------------------------------------------------------------

### Added

- **One root config key, `guardCore`.** It is a map that holds `enabled` plus every setting that lived in `guard`, Redis settings included, so nothing collides with the config keys of other extensions. It is declared at level `system`, so the Settings API never returns it, and EspoCRM stores it in `data/config-internal.php`.
- **An automatic migration on upgrade.** A new `AfterInstall` script moves `guardEnabled` and `guard` into `guardCore` (`enabled` first, then the settings) and removes the old keys. It changes nothing on a fresh install, when `guardCore` is already set, or when `guard` is not a map (the middleware reports that one loudly). Running it twice changes nothing.
- **A docs page on route policies** (#15): how a request finds its route, what switching a default route off does, and every route option and behavior rule key with its unit.

### Changed

- **`guardEnabled` and `guard` are deprecated.** They keep working, and the guard logs one warning when it is built, pointing to docs/configuration.md. Setting `guardCore` and an old key at the same time while the guard is on throws an error that asks to move everything under `guardCore`. When both say the guard is off, nothing happens.
- **The uninstall also removes `guardCore`.** An upgrade does not run the old version's uninstall script, so the migration is what moves the old keys.
- **The docs, the examples and the live smoke checks use `guardCore`.** The configuration docs say to put it in `data/config-internal.php`, because EspoCRM stores level `system` parameters there and rewrites them there on any config save.

### Fixed

- **Uninstall and the migration now clear the old keys from both config files.** On EspoCRM 9.0 to 9.2, removing a level `system` parameter only touched `data/config-internal.php`, so uninstalling left `guardEnabled` and `guard` in `data/config.php`, where they lose their `system` level and the Settings API can return them. The migration had the same gap and would have left them next to `guardCore`. Both scripts now remove them from both files.
- **The migration leaves old keys in an override file alone.** EspoCRM never writes `data/config-override.php` or `data/config-internal-override.php`, so when either holds `guardEnabled` or `guard` nothing is migrated and the old keys keep working with the deprecation warning.

### Test

- New unit tests run both scripts against EspoCRM's real `Config` and `ConfigWriter` and assert the file contents (keys in `config.php`, in `config-internal.php`, split across both, in an override file, a fresh install, a second run and an uninstall).
- New unit tests cover the `guardCore` resolution (enabled, disabled, missing `enabled`, not a bool, not a map, split settings), the deprecation warning once per build, every case of the migration including a second run, and the three-key uninstall. Line coverage of the module stays at 100%.

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
