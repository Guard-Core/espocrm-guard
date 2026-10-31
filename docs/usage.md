# Usage and default policy

With only `guardEnabled` set to true you get the default policy:

- Passive mode: what would be blocked is logged, nothing is blocked.
- Attack screening of paths, query strings, headers and JSON or form bodies is on.
- No body scan on `POST /api/v1/Attachment/chunk/`.
- A ban rule after more than 10 responses with status 401 in 5 minutes on `GET /api/v1/App/user` and on every other API path (counted per path).
- Rate limits on `POST /api/v1/LeadCapture/` (10 per minute) and on the two password routes (5 per 5 minutes).

## Rolling out

1. Set `'guardEnabled' => true` and clear the cache.
2. Watch `data/logs` for `Guard would block by ...` lines. Each line names the check, the reason and the client, and the request still passes.
3. Fix or exclude what is a false positive, for example with a route policy.
4. Set `'passiveMode' => false`. Blocked requests now log `Guard blocked by ...` and get the engine's response (400 for a detected attack, 403 for a banned client, 429 for a rate limit).

Redis matters for steps 3 and 4: without it the rate limits and bans do not hold across requests.

## Route policies

[Route policies](routes.md) explains how a request finds its route, what switching a default route off does, and every option and behavior rule key with its unit.

```php
'guard' => [
    'routes' => [
        // A stricter lead capture limit.
        'POST /api/v1/LeadCapture/' => ['rateLimit' => 3, 'rateLimitWindow' => 60],
        // Switch a default route off.
        'POST /api/v1/User/passwordChangeRequest' => null,
        // A ban after repeated 401 responses.
        'GET /api/v1/App/user' => ['behaviorRules' => [[
            'rule_type' => 'return_pattern',
            'pattern' => 'status:401',
            'threshold' => 3,
            'window' => 300,
            'action' => 'ban',
            'ban_duration' => 3600,
        ]]],
    ],
],
```

## Examples

`examples/simple_app` runs a real EspoCRM with MariaDB and the default policy. `examples/advanced_app` adds Redis, an nginx reverse proxy with `trustedProxies`, passive mode off and the route policies above. Both install the built extension zip through EspoCRM's own extension install, and both are exercised by the Live Smoke workflow.
