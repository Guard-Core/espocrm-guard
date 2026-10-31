# Configuration

Add one key, `guardCore`, to `data/config-internal.php`. EspoCRM stores level `system` config parameters there and rewrites them there on any config save, so a copy kept in `data/config.php` can end up shadowed or moved. The key is not editable in the admin UI and is never sent to the front end. It is the only root key the extension owns, so nothing collides with other extensions.

```php
'guardCore' => [
    'enabled' => true,
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

Settings you give override the defaults key by key. A route key is an optional method plus a path, and a `null` route entry switches a default route off. `enabled` switches the guard on (the default is off). Every other engine option is a key of `guardCore` (the named arguments of the engine's `SecurityConfig`), and every route option a key of a route (`RouteConfig`). An unknown key or an invalid route option throws instead of being ignored. Options that need a closure or an object (`onBlock`, `authVerifier`, `customRequestCheck` and similar) cannot be expressed in a config file and are rejected.

## Upgrading from 1.0.0

Version 1.0.0 used two root keys, `guardEnabled` and `guard`. Upgrading the extension moves them automatically when they are in `data/config.php` or `data/config-internal.php`, which is where the 1.0.0 docs said to put them: the upgrade runs a script that writes `guardCore` into `data/config-internal.php` with `enabled` set from `guardEnabled` and every setting of `guard`, then removes the old keys from both files.

- Old keys in `data/config-override.php` or `data/config-internal-override.php` are left alone, because EspoCRM never writes those files. The upgrade changes nothing while either one holds `guardEnabled` or `guard`, and the old keys keep working with the deprecation warning until you move them under `guardCore` yourself.
- Old keys that you add by hand later still work. The guard logs one deprecation warning when it starts, naming this page.
- Setting `guardCore` and one of the old keys at the same time while the guard is on is an error: the middleware throws and says to move everything under `guardCore` and remove the old keys. When both say the guard is off, nothing happens.
- If `guard` is not a map, the upgrade leaves the config as it is, and the middleware reports it loudly when the guard is on.

## Redis

Redis is off by default. Without it, rate limits, behavior rules and bans keep their state per request, so under PHP-FPM they never trigger across requests. Set `enableRedis` and `redisUrl`. Only `redis://host[:port]` is supported: a URL with credentials, a database index or `rediss` is rejected, because the engine's Redis client connects by host and port only.

## Trusted proxies and `ipAddressServerParam`

The guard identifies the client by `REMOTE_ADDR`. EspoCRM's `ipAddressServerParam` is not read. If you set it to something else (for example `HTTP_X_FORWARDED_FOR`), the guard logs a warning, and behind a proxy you must set `guardCore.trustedProxies` (and `guardCore.trustedProxyDepth`) so the client is resolved from the forwarded header. Without that every client behind the proxy looks like the proxy.

## Country filtering

The optional `ipinfo` map (`token`, and optionally `dbPath` and `maxAge`) turns on the IPInfo country database for the engine's country rules.

## Secrets

`guardCore` can hold a Redis URL and an agent API key. The extension declares it (and the deprecated `guardEnabled` and `guard`) at level `system`, so EspoCRM never returns them from `GET /api/v1/Settings`, for any user.
