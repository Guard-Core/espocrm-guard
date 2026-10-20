# Installation

1. Download `guard-core-<version>.zip` from the [GitHub release](https://github.com/Guard-Core/espocrm-guard/releases), together with its `.sha256` file, or build it (see below).
2. Check the download with `sha256sum -c guard-core-<version>.zip.sha256`.
3. In EspoCRM open Administration > Extensions, upload the zip and click Install.

From the command line the same install is `php command.php extension --file=guard-core-<version>.zip`, run in the EspoCRM root directory.

Installing changes nothing: `guardEnabled` is absent, so the middleware passes every request straight through. See [Usage and default policy](usage.md) to turn it on.

## Uninstall

Uninstalling removes the middleware and also deletes `guardEnabled` and `guard` from the config files. That is on purpose: the extension hides `guard` (it can hold a Redis URL and an agent API key) from the Settings API, and once it is gone nothing would hide those values from logged-in users. Keep a copy of your `guard` block if you plan to reinstall.

## Build from source

```bash
npm ci
npm run extension      # builds build/guard-core-<version>.zip
```

The module bundles only `rennf93/psr15-guard` and `rennf93/guard-core-php`. The `psr/*` packages that psr15-guard requires are provided by EspoCRM itself, so the module `composer.json` replaces them and none ends up in the package. A size and content check runs in CI on every build.
