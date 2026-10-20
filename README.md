# Guard Core for EspoCRM

Application-layer security for the EspoCRM API, built on [psr15-guard](https://packagist.org/packages/rennf93/psr15-guard) and the [guard-core-php](https://packagist.org/packages/rennf93/guard-core-php) engine (both MIT). It screens API requests for attacks, bans clients that misbehave, applies per-route policies and has a passive mode that logs what it would block without blocking anything. It is off until you enable it, and while off it adds one config lookup per request.

The extension registers one global API middleware (`globalMiddlewareClassNameList`), so it covers `public/api/v1/` and the portal API. The guard runs inside the application, so it sees what a perimeter device cannot: the parsed JSON body, the route and the response status. It complements a reverse proxy or WAF, it does not replace one.

## Requirements

- EspoCRM 9.0.0 or later.
- PHP 8.2 or later.
- Redis, optional, for rate limits, behavior rules and bans that hold across requests (see below).

## Install

1. Download `guard-core-1.0.0.zip` (or build it, see Development).
2. In EspoCRM open Administration > Extensions, upload the zip and click Install.

Installing changes nothing: `guardEnabled` is absent, so the middleware passes every request straight through. Uninstalling removes the middleware and also deletes `guardEnabled` and `guard` from the config files. That is on purpose: the extension hides `guard` (it can hold a Redis URL and an agent API key) from the Settings API, and once it is gone nothing would hide those values from logged-in users. Keep a copy of your `guard` block if you plan to reinstall.

## Configure

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

### Telemetry agent

The [guard-agent-php](https://packagist.org/packages/rennf93/guard-agent-php) agent is optional and is not bundled. To use it, run in the EspoCRM root directory:

```
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

## Development

```
npm install
npm run prepare-test   # fetches an EspoCRM instance into site/ for tests and static analysis
npm run sync           # copies the extension into site/ and installs the module libraries
composer install
vendor/bin/phpunit
vendor/bin/phpstan --memory-limit=1024M
npm run extension      # builds build/guard-core-<version>.zip
```

One unit test needs Redis on 127.0.0.1:6420 and skips without it.

The module bundles only `rennf93/psr15-guard` and `rennf93/guard-core-php`. The `psr/*` packages that psr15-guard requires are provided by EspoCRM itself, so the module `composer.json` replaces them and none ends up in the package.

## License

MIT, see `LICENSE`.
