# Guard Core for EspoCRM

Guard Core for EspoCRM is an EspoCRM extension that adds application-layer security to the EspoCRM API. It is built on [psr15-guard](https://packagist.org/packages/rennf93/psr15-guard) and the [guard-core-php](https://packagist.org/packages/rennf93/guard-core-php) engine (both MIT).

It screens API requests for attacks, bans clients that misbehave, applies per-route policies and has a passive mode that logs what it would block without blocking anything. It is off until you enable it, and while off it adds one config lookup per request.

The extension registers one global API middleware (`globalMiddlewareClassNameList`), so it covers `public/api/v1/` and the portal API. The guard runs inside the application, so it sees what a perimeter device cannot: the parsed JSON body, the route and the response status. It complements a reverse proxy or WAF, it does not replace one.

This extension holds no security logic. Detection, rate limiting, bans, headers and responses live in the engine. The extension reads the `guardCore` config, wires the engine to EspoCRM's logger and config, and registers the middleware.

## Requirements

- EspoCRM 9.0.0 or later.
- PHP 8.2 or later.
- Redis, optional, for rate limits, behavior rules and bans that hold across requests.

## Where to go next

- [Installation](installation.md): the zip, the upload and what uninstalling does.
- [Configuration](configuration.md): the `guardCore` keys, Redis, trusted proxies.
- [Usage and default policy](usage.md): what you get with only `guardCore.enabled` set, and how to roll out from passive mode.
- [Route policies](routes.md): how routes match, every route option, behavior rules (types, patterns, units, actions).
- [Telemetry agent](telemetry-agent.md): the optional agent.
- [What it covers and does not](coverage.md): the entry points and the known limits.
