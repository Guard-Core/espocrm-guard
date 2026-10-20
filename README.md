<p align="center">
    <a href="https://guard-core.github.io/guard-core/latest/">
        <img src="https://guard-core.github.io/guard-core/latest/assets/guard_core_legend.svg" alt="Guard Core">
    </a>
</p>

___

<p align="center">
    <strong>EspoCRM extension for the guard-core-php engine: registers the psr15-guard middleware on the EspoCRM API with attack screening, behavior bans, per-route policies and a passive mode. Off by default, with its settings hidden from the front end.</strong>
</p>

<p align="center">
    <a href="https://github.com/Guard-Core/espocrm-guard/releases/latest">
        <img src="https://img.shields.io/github/v/release/Guard-Core/espocrm-guard?color=0080ff" alt="Release version">
    </a>
    <a href="https://guard-core.github.io/espocrm-guard/">
        <img src="https://img.shields.io/badge/docs-latest-0080ff.svg" alt="Docs">
    </a>
    <a href="https://github.com/Guard-Core/espocrm-guard/actions/workflows/release.yml">
        <img src="https://github.com/Guard-Core/espocrm-guard/actions/workflows/release.yml/badge.svg" alt="Release">
    </a>
    <a href="https://opensource.org/licenses/MIT">
        <img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License">
    </a>
    <a href="https://github.com/Guard-Core/espocrm-guard/actions/workflows/ci.yml">
        <img src="https://github.com/Guard-Core/espocrm-guard/actions/workflows/ci.yml/badge.svg" alt="CI">
    </a>
</p>

<p align="center">
    <a href="https://github.com/Guard-Core/espocrm-guard/actions/workflows/pages/pages-build-deployment">
        <img src="https://github.com/Guard-Core/espocrm-guard/actions/workflows/pages/pages-build-deployment/badge.svg?branch=gh-pages" alt="PagesBuildDeployment">
    </a>
    <a href="https://github.com/Guard-Core/espocrm-guard/actions/workflows/docs.yml">
        <img src="https://github.com/Guard-Core/espocrm-guard/actions/workflows/docs.yml/badge.svg" alt="DocsUpdate">
    </a>
    <img src="https://img.shields.io/github/last-commit/Guard-Core/espocrm-guard?style=flat&amp;logo=git&amp;logoColor=white&amp;color=0080ff" alt="last-commit">
</p>

<p align="center">
    <img src="https://img.shields.io/badge/PHP-777BB4.svg?style=flat&logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/EspoCRM-9.0%2B-0080ff.svg?style=flat" alt="EspoCRM">
    <a href="https://github.com/Guard-Core/espocrm-guard/releases">
        <img src="https://img.shields.io/github/downloads/Guard-Core/espocrm-guard/total" alt="Downloads">
    </a>
</p>

<p align="center">
    <a href="https://guard-core.com">Website</a> &middot;
    <a href="https://guard-core.github.io/espocrm-guard/">Docs</a> &middot;
    <a href="https://playground.guard-core.com">Playground</a> &middot;
    <a href="https://app.guard-core.com">Dashboard</a> &middot;
    <a href="https://discord.gg/ZW7ZJbjMkK">Discord</a>
</p>

---

## Ecosystem

Guard Core is the Python engine. Framework adapters are thin wrappers that translate native request/response types into Guard Core's protocols. The telemetry agents ship security events and metrics to the monitoring backend. Parallel engine implementations exist for Go, PHP, TypeScript (on npm), and Rust (on crates.io) - all ports of the same reference semantics, conformance-tested against the shared adversarial corpus.

### Python

| Package | Role | PyPI |
|---|---|---|
| [guard-core](https://github.com/Guard-Core/guard-core) | Framework-agnostic security engine | [![PyPI](https://img.shields.io/pypi/v/guard-core)](https://pypi.org/project/guard-core/) |
| [guard-agent](https://github.com/Guard-Core/guard-agent) | Telemetry agent | [![PyPI](https://img.shields.io/pypi/v/guard-agent)](https://pypi.org/project/guard-agent/) |
| [fastapi-guard](https://github.com/Guard-Core/fastapi-guard) | FastAPI / Starlette adapter | [![PyPI](https://img.shields.io/pypi/v/fastapi-guard)](https://pypi.org/project/fastapi-guard/) |
| [flaskapi-guard](https://github.com/Guard-Core/flaskapi-guard) | Flask adapter | [![PyPI](https://img.shields.io/pypi/v/flaskapi-guard)](https://pypi.org/project/flaskapi-guard/) |
| [djapi-guard](https://github.com/Guard-Core/djapi-guard) | Django adapter | [![PyPI](https://img.shields.io/pypi/v/djapi-guard)](https://pypi.org/project/djapi-guard/) |
| [tornadoapi-guard](https://github.com/Guard-Core/tornadoapi-guard) | Tornado adapter | [![PyPI](https://img.shields.io/pypi/v/tornadoapi-guard)](https://pypi.org/project/tornadoapi-guard/) |

### Go

Go modules published via GitHub releases. **Production-ready.**

| Package | Role | Release |
|---|---|---|
| [guard-core-go](https://github.com/Guard-Core/guard-core-go) | Go engine | [![release](https://img.shields.io/github/v/tag/Guard-Core/guard-core-go?label=tag)](https://github.com/Guard-Core/guard-core-go/releases) |
| [nethttp-guard](https://github.com/Guard-Core/nethttp-guard) | net/http adapter | [![release](https://img.shields.io/github/v/tag/Guard-Core/nethttp-guard?label=tag)](https://github.com/Guard-Core/nethttp-guard/releases) |
| [gin-guard](https://github.com/Guard-Core/gin-guard) | Gin adapter | [![release](https://img.shields.io/github/v/tag/Guard-Core/gin-guard?label=tag)](https://github.com/Guard-Core/gin-guard/releases) |
| [echo-guard](https://github.com/Guard-Core/echo-guard) | Echo (v4) adapter | [![release](https://img.shields.io/github/v/tag/Guard-Core/echo-guard?label=tag)](https://github.com/Guard-Core/echo-guard/releases) |
| [fiber-guard](https://github.com/Guard-Core/fiber-guard) | Fiber (v3) adapter | [![release](https://img.shields.io/github/v/tag/Guard-Core/fiber-guard?label=tag)](https://github.com/Guard-Core/fiber-guard/releases) |
| [guard-agent-go](https://github.com/Guard-Core/guard-agent-go) | Telemetry agent | [![release](https://img.shields.io/github/v/tag/Guard-Core/guard-agent-go?label=tag)](https://github.com/Guard-Core/guard-agent-go/releases) |

### PHP

Published on [Packagist](https://packagist.org/) under the `rennf93` vendor. **Production-ready.**

| Package | Role | Packagist |
|---|---|---|
| [guard-core-php](https://github.com/Guard-Core/guard-core-php) | PHP engine | [![Packagist](https://img.shields.io/packagist/v/rennf93/guard-core-php)](https://packagist.org/packages/rennf93/guard-core-php) |
| [laravel-guard](https://github.com/Guard-Core/laravel-guard) | Laravel adapter | [![Packagist](https://img.shields.io/packagist/v/rennf93/laravel-guard)](https://packagist.org/packages/rennf93/laravel-guard) |
| [symfony-guard](https://github.com/Guard-Core/symfony-guard) | Symfony adapter | [![Packagist](https://img.shields.io/packagist/v/rennf93/symfony-guard)](https://packagist.org/packages/rennf93/symfony-guard) |
| [psr15-guard](https://github.com/Guard-Core/psr15-guard) | PSR-15 adapter | [![Packagist](https://img.shields.io/packagist/v/rennf93/psr15-guard)](https://packagist.org/packages/rennf93/psr15-guard) |
| [slim-guard](https://github.com/Guard-Core/slim-guard) | Slim 4 adapter | [![Packagist](https://img.shields.io/packagist/v/rennf93/slim-guard)](https://packagist.org/packages/rennf93/slim-guard) |
| [guard-agent-php](https://github.com/Guard-Core/guard-agent-php) | Telemetry agent | [![Packagist](https://img.shields.io/packagist/v/rennf93/guard-agent-php)](https://packagist.org/packages/rennf93/guard-agent-php) |

### TypeScript / JavaScript

Published under the [`@guardcore`](https://www.npmjs.com/org/guardcore) npm scope; source in the [guard-core-ts](https://github.com/Guard-Core/guard-core-ts) monorepo. **Production-ready.**

| Package | Role | npm |
|---|---|---|
| | [@guardcore/core](https://github.com/Guard-Core/guard-core-ts/tree/master/packages/core) | Core engine | [![npm](https://img.shields.io/npm/v/@guardcore%2Fcore)](https://www.npmjs.com/package/@guardcore/core) |
| [@guardcore/express](https://github.com/Guard-Core/guard-core-ts/tree/master/packages/express) | Express adapter | [![npm](https://img.shields.io/npm/v/@guardcore%2Fexpress)](https://www.npmjs.com/package/@guardcore/express) |
| [@guardcore/nestjs](https://github.com/Guard-Core/guard-core-ts/tree/master/packages/nestjs) | NestJS adapter | [![npm](https://img.shields.io/npm/v/@guardcore%2Fnestjs)](https://www.npmjs.com/package/@guardcore/nestjs) |
| [@guardcore/fastify](https://github.com/Guard-Core/guard-core-ts/tree/master/packages/fastify) | Fastify adapter | [![npm](https://img.shields.io/npm/v/@guardcore%2Ffastify)](https://www.npmjs.com/package/@guardcore/fastify) |
| [@guardcore/hono](https://github.com/Guard-Core/guard-core-ts/tree/master/packages/hono) | Hono (edge) adapter | [![npm](https://img.shields.io/npm/v/@guardcore%2Fhono)](https://www.npmjs.com/package/@guardcore/hono) |
| [guardagent](https://github.com/Guard-Core/guard-agent-ts) | Telemetry agent | [![npm](https://img.shields.io/npm/v/guardagent)](https://www.npmjs.com/package/guardagent) |

### Rust

Published on crates.io. **Production-ready.**

| Package | Role | crates.io |
|---|---|---|
| [guard-core-engine](https://github.com/Guard-Core/guard-core-rs) | Core engine crate | [![crates.io](https://img.shields.io/crates/v/guard-core-engine)](https://crates.io/crates/guard-core-engine) |
| [guard-core-rs](https://github.com/Guard-Core/guard-core-rs) | Facade crate (consumer entry point) | [![crates.io](https://img.shields.io/crates/v/guard-core-rs)](https://crates.io/crates/guard-core-rs) |
| [actix-guard-rs](https://github.com/Guard-Core/actix-guard-rs) | Actix Web adapter | [![crates.io](https://img.shields.io/crates/v/actix-guard-rs)](https://crates.io/crates/actix-guard-rs) |
| [axum-guard-rs](https://github.com/Guard-Core/axum-guard-rs) | Axum adapter | [![crates.io](https://img.shields.io/crates/v/axum-guard-rs)](https://crates.io/crates/axum-guard-rs) |
| [tower-guard-rs](https://github.com/Guard-Core/tower-guard-rs) | Tower adapter | [![crates.io](https://img.shields.io/crates/v/tower-guard-rs)](https://crates.io/crates/tower-guard-rs) |
| [rocket-guard-rs](https://github.com/Guard-Core/rocket-guard-rs) | Rocket adapter | [![crates.io](https://img.shields.io/crates/v/rocket-guard-rs)](https://crates.io/crates/rocket-guard-rs) |
| [guard-agent-rs](https://github.com/Guard-Core/guard-agent-rs) | Telemetry agent | [![crates.io](https://img.shields.io/crates/v/guard-agent-rs)](https://crates.io/crates/guard-agent-rs) |

### AI Coding Agents

| Package | Role | PyPI |
|---|---|---|
| [guard-core-mcp](https://github.com/Guard-Core/guard-core-mcp) | MCP server: config validation, docs search, detection sandbox | [![PyPI](https://img.shields.io/pypi/v/guard-core-mcp)](https://pypi.org/project/guard-core-mcp/) |

___

## Features

- **Attack screening** of paths, query strings, headers and JSON or form bodies, with a passive mode that logs what it would block without blocking anything
- **Behavior bans** after repeated misbehavior, such as more than 10 responses with status 401 in 5 minutes
- **Per-route policies**: rate limits, ban rules and scan options for the endpoints that are internet facing or credential related
- **Off until you enable it**: while off it adds one config lookup per request, and its settings are hidden from the Settings API
- **Optional Redis** for rate limits, behavior rules and bans that hold across requests, and an optional telemetry agent

___

## Documentation

📚 **[Documentation](https://guard-core.github.io/espocrm-guard/)** - full documentation for this extension.

🛡️ **[Guard Core](https://guard-core.github.io/guard-core/latest/)** - the engine's reference documentation.

🤖 **[Monitoring Agent Integration](https://github.com/Guard-Core/guard-agent)** - monitor your Guard instance with a monitoring agent.

___

## Install

Application-layer security for the EspoCRM API, built on [psr15-guard](https://packagist.org/packages/rennf93/psr15-guard) and the [guard-core-php](https://packagist.org/packages/rennf93/guard-core-php) engine (both MIT). It screens API requests for attacks, bans clients that misbehave, applies per-route policies and has a passive mode that logs what it would block without blocking anything. It is off until you enable it, and while off it adds one config lookup per request.

The extension registers one global API middleware (`globalMiddlewareClassNameList`), so it covers `public/api/v1/` and the portal API. The guard runs inside the application, so it sees what a perimeter device cannot: the parsed JSON body, the route and the response status. It complements a reverse proxy or WAF, it does not replace one.

### Requirements

- EspoCRM 9.0.0 or later.
- PHP 8.2 or later.
- Redis, optional, for rate limits, behavior rules and bans that hold across requests (see below).

### Steps

1. Download `guard-core-<version>.zip` from the [latest release](https://github.com/Guard-Core/espocrm-guard/releases/latest) (or build it, see Testing).
2. In EspoCRM open Administration > Extensions, upload the zip and click Install.

Installing changes nothing: `guardEnabled` is absent, so the middleware passes every request straight through. Uninstalling removes the middleware and also deletes `guardEnabled` and `guard` from the config files. That is on purpose: the extension hides `guard` (it can hold a Redis URL and an agent API key) from the Settings API, and once it is gone nothing would hide those values from logged-in users. Keep a copy of your `guard` block if you plan to reinstall.

## Usage

Add the keys to `data/config.php` (or `data/config-internal.php`). They are not editable in the admin UI and are never sent to the front end.

```php
'guardEnabled' => true,
'guard' => [
    'enableRedis' => true,
    'redisUrl' => 'redis://127.0.0.1:6379',
    'redisPrefix' => 'espocrm_guard:',
    'trustedProxies' => ['10.0.0.0/8'],
    'routes' => [
        'POST /api/v1/LeadCapture/' => ['rateLimit' => 5, 'rateLimitWindow' => 60],
    ],
],
```

Clear the cache after changing the config (`php command.php clear-cache`).

With only `guardEnabled` set to true you get the default policy:

- Passive mode: what would be blocked is logged, nothing is blocked.
- Attack screening of paths, query strings, headers and JSON or form bodies is on.
- No body scan on `POST /api/v1/Attachment/chunk/`.
- A ban rule after more than 10 responses with status 401 in 5 minutes on `GET /api/v1/App/user` and on the API prefix.
- Rate limits on `POST /api/v1/LeadCapture/` (10 per minute) and on the two password routes (5 per 5 minutes).

Start in passive mode, watch `data/logs` for `Guard would block by ...` lines, then set `'passiveMode' => false`. Settings you give override the defaults key by key. A route key is an optional method plus a path, and a `null` route entry switches a default route off. Every engine option is a key of `guard` (the named arguments of the engine's `SecurityConfig`), and every route option a key of a route (`RouteConfig`). An unknown key or an invalid route option throws instead of being ignored. Options that need a closure or an object (`onBlock`, `authVerifier`, `customRequestCheck` and similar) cannot be expressed in a config file and are rejected.

### Redis

Redis is off by default. Without it, rate limits, behavior rules and bans keep their state per request, so under PHP-FPM they never trigger across requests. Set `enableRedis` and `redisUrl`. Only `redis://host[:port]` is supported: a URL with credentials, a database index or `rediss` is rejected, because the engine's Redis client connects by host and port only.

### Trusted proxies and `ipAddressServerParam`

The guard identifies the client by `REMOTE_ADDR`. EspoCRM's `ipAddressServerParam` is not read. If you set it to something else (for example `HTTP_X_FORWARDED_FOR`), the guard logs a warning, and behind a proxy you must set `guard.trustedProxies` (and `guard.trustedProxyDepth`) so the client is resolved from the forwarded header. Without that every client behind the proxy looks like the proxy.

### Examples

[`examples/simple_app`](examples/simple_app) runs a real EspoCRM with MariaDB and the default policy. [`examples/advanced_app`](examples/advanced_app) adds Redis, an nginx reverse proxy with `trustedProxies`, passive mode off and custom route policies. Both install the built extension zip through EspoCRM's own extension install, and the Live Smoke workflow runs both.

## Telemetry agent

The [guard-agent-php](https://packagist.org/packages/rennf93/guard-agent-php) agent is optional and is not bundled. To use it, run in the EspoCRM root directory:

```bash
composer require rennf93/guard-agent-php
```

then add an `agent` map to `guard`. The extension loads the agent only when the package is present, and fails with a clear message when `agent` is configured without it. An EspoCRM upgrade replaces `vendor/`, so run the command again after upgrading.

## What it covers

The Slim API entry points: `public/api/v1/` and the portal API, through the global middleware.

## What it does not cover

- Non-Slim entry points such as `?entryPoint=...` URLs and the OAuth endpoints.
- Default route keys are literal `/api/v1/...`. A subdirectory install or portal-specific routes need your own route keys.
- A honeypot field on the public lead capture form needs a hidden input in the form view. The server side (`honeypotFields` on the route) works, the client part is not part of this extension.
- Header exclusion from logs (`logSensitiveHeaders`) is not in the default policy: guard-core-php 4.4.0 throws a TypeError for a non-empty value. The engine still redacts `Authorization`, `X-Api-Key`, `Proxy-Authorization` and `Cookie` in its logs.

## Testing

```bash
npm ci
composer install
make prepare-test      # builds site/, a sparse EspoCRM checkout for tests and static analysis
make lint              # php -l, composer validate --strict, PHPStan level 8
make local-test        # PHPStan, then PHPUnit with the 100% line coverage gate
make test              # the same in Docker with a private Redis
make extension         # builds build/guard-core-<version>.zip
```

One unit test needs Redis on 127.0.0.1:6420 (`REDIS_HOST`, `REDIS_PORT`) and skips without it. CI runs PHP 8.2 to 8.5 against EspoCRM 9.0.0 and the stable release, plus the live smoke runs of both examples. See [CONTRIBUTING.md](CONTRIBUTING.md).

The module bundles only `rennf93/psr15-guard` and `rennf93/guard-core-php`. The `psr/*` packages that psr15-guard requires are provided by EspoCRM itself, so the module `composer.json` replaces them and none ends up in the package.

## License

MIT, see `LICENSE`.
