# Configuration

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

Settings you give override the defaults key by key. A route key is an optional method plus a path, and a `null` route entry switches a default route off. Every engine option is a key of `guard` (the named arguments of the engine's `SecurityConfig`), and every route option a key of a route (`RouteConfig`). An unknown key or an invalid route option throws instead of being ignored. Options that need a closure or an object (`onBlock`, `authVerifier`, `customRequestCheck` and similar) cannot be expressed in a config file and are rejected.

## Redis

Redis is off by default. Without it, rate limits, behavior rules and bans keep their state per request, so under PHP-FPM they never trigger across requests. Set `enableRedis` and `redisUrl`. Only `redis://host[:port]` is supported: a URL with credentials, a database index or `rediss` is rejected, because the engine's Redis client connects by host and port only.

## Trusted proxies and `ipAddressServerParam`

The guard identifies the client by `REMOTE_ADDR`. EspoCRM's `ipAddressServerParam` is not read. If you set it to something else (for example `HTTP_X_FORWARDED_FOR`), the guard logs a warning, and behind a proxy you must set `guard.trustedProxies` (and `guard.trustedProxyDepth`) so the client is resolved from the forwarded header. Without that every client behind the proxy looks like the proxy.

## Country filtering

The optional `ipinfo` map (`token`, and optionally `dbPath` and `maxAge`) turns on the IPInfo country database for the engine's country rules.

## Secrets

`guard` can hold a Redis URL and an agent API key. The extension declares both config keys at level `system`, so EspoCRM never returns them from `GET /api/v1/Settings`, for any user.
