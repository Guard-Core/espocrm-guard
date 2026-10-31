# Route policies

A route policy is a set of extra rules for one API path: a rate limit, a ban rule, a size limit, a scan exclusion. You add them under `guardCore.routes` in `data/config-internal.php` and clear the cache (`php command.php clear-cache`).

```php
'guardCore' => [
    'enabled' => true,
    'routes' => [
        'POST /api/v1/LeadCapture/' => ['rateLimit' => 3, 'rateLimitWindow' => 60],
    ],
],
```

Requests that match no route policy still get everything set at the `guardCore` level (attack screening, bans, the global rate limit).

## How a request finds its policy

- A key is an optional HTTP method plus a path: `'POST /api/v1/LeadCapture/'` or `'/api/v1/'`. Without a method the key matches every method.
- The path matches exactly, or as a prefix when it ends with `/`. `'/api/v1/'` matches every API path, `'GET /api/v1/App/user'` only that one.
- The longest matching path wins. On the same path, a key with a method wins over one without.
- A request gets exactly one route policy. A more specific route replaces the options of a prefix route, it does not add to them. For example the default `POST /api/v1/LeadCapture/` route has only a rate limit, so the repeated-401 ban rule of the `/api/v1/` route does not apply to lead capture requests. If you want both, put both in the specific route.

## Default routes

With only `guardCore.enabled` set, these routes are active:

| Route | What it does |
| --- | --- |
| `/api/v1/` | Bans a client for 1 hour after more than 10 responses with status 401 in 5 minutes on the same path |
| `GET /api/v1/App/user` | The same ban rule (this is the login call) |
| `POST /api/v1/Attachment/chunk/` | No body scan (file chunks), plus the same ban rule |
| `POST /api/v1/LeadCapture/` | At most 10 requests per minute per client |
| `POST /api/v1/User/passwordChangeRequest` | At most 5 requests per 5 minutes per client |
| `POST /api/v1/User/changePasswordByRequest` | At most 5 requests per 5 minutes per client |

## Changing or switching off a default route

- A route you set with the same key as a default route replaces it as a whole. Options of the default route that you leave out are gone, so repeat the ones you want to keep.
- A route set to `null` switches the default route off: the extension removes it, and requests to that path fall back to the next matching route, usually `/api/v1/`.

```php
'guardCore' => [
    'enabled' => true,
    'routes' => [
        // Lead capture: 3 requests per minute instead of 10.
        'POST /api/v1/LeadCapture/' => ['rateLimit' => 3, 'rateLimitWindow' => 60],
        // No more 5 per 5 minutes limit on password reset requests.
        // The path now falls back to the /api/v1/ route (only the 401 ban rule).
        'POST /api/v1/User/passwordChangeRequest' => null,
    ],
],
```

## Behavior rules

A behavior rule counts what one client does on one path and acts when the count goes over a threshold.

```php
'GET /api/v1/App/user' => ['behaviorRules' => [[
    'rule_type' => 'return_pattern', // count responses that match the pattern
    'pattern' => 'status:401',       // responses with status 401
    'threshold' => 3,                // act when the count goes above 3 (on the 4th)
    'window' => 300,                 // within the last 300 seconds
    'action' => 'ban',               // ban the client's IP
    'ban_duration' => 3600,          // for 3600 seconds
]]],
```

| Key | Values | Default |
| --- | --- | --- |
| `rule_type` | `return_pattern`: counts responses to the client that match `pattern`. `usage` and `frequency`: count the client's requests to the path (both behave the same in this version) | required |
| `threshold` | Whole number, 1 or more. The action runs when the count goes above it | required |
| `window` | Seconds, a sliding window. `0` means the default | `3600` |
| `pattern` | Only for `return_pattern`, see below | none |
| `action` | `ban`, `log`, `alert` or `throttle`, see below | `log` |
| `ban_duration` | Seconds the ban lasts, for `action` `ban` | `3600` |

Patterns for `return_pattern`:

| Pattern | Matches |
| --- | --- |
| `status:401` | Responses with that status code. Any code works: `status:403`, `status:404` (404 scanning), `status:429` |
| `json:path.to.field==value` | A JSON response whose field equals the value, case insensitive. A last segment ending in `[]` matches any element of an array: `json:tags[]==admin` |
| `regex:...` | A response body that matches the regular expression, case insensitive |
| any other text | A response body that contains the text, case insensitive |

Only `status:` patterns work out of the box. The other three read the response body, so they need `'behaviorScanResponseBody' => true` at the `guardCore` level; without it a route rule with such a pattern never matches. The body is read up to 262144 bytes (`behaviorMaxResponseBodyInspectBytes`).

Actions:

| Action | What happens |
| --- | --- |
| `ban` | The client's IP is banned for `ban_duration` seconds. Every API request from it then gets a 403, on every route (unless a route sets `'bypassedChecks' => ['ip_ban']`) |
| `log` | A warning line in `data/logs` |
| `alert` | A critical line in `data/logs` |
| `throttle` | In the engine version this extension ships (guard-core-php 4.4.0) it only writes a log line. It does not slow down or block the client |

Things to know:

- Counts are kept per client IP and per method plus exact path, not per route key. The `/api/v1/` default rule counts the 401 responses of each path separately.
- The rule acts after the request that crosses the threshold is answered: that request goes through, the ban applies from the next one.
- In passive mode (the default) nothing is banned. You get a `[PASSIVE MODE] Would ban IP ...` line instead.
- Without Redis the counts start from zero on every request under PHP-FPM, so a rule never fires. Set `enableRedis` and `redisUrl` (see [Configuration](configuration.md#redis)).
- `correlate_with_detection` is accepted but only changes global rules (`globalBehaviorRules` at the `guardCore` level), not route rules.

## All route options

Every key below goes inside a route, next to `behaviorRules`. An unknown key makes the extension throw when it builds the guard, so a typo in a key name shows up as an error, not as a silent no-op. The status codes are what the client gets with `'passiveMode' => false`; in passive mode the request passes and the would-be block is logged.

### Limits

| Option | Example | What it does |
| --- | --- | --- |
| `rateLimit` | `10` | At most this many requests per client IP on this path per window, then 429. The global limit still applies too |
| `rateLimitWindow` | `60` | The window for `rateLimit`, in seconds. Default 60 |
| `geoRateLimits` | `['DE' => ['limit' => 20, 'window' => 60], '*' => ['limit' => 5, 'window' => 60]]` | A limit per client country, `*` for every other country. Window in seconds. Needs `ipinfo`, without it the whole option is skipped |
| `maxRequestSize` | `1048576` | Bytes. A larger `Content-Length` gets 413. A request without `Content-Length` (chunked) is not checked |
| `allowedContentTypes` | `['application/json']` | Any other `Content-Type` gets 415. Lowercase, exact match, parameters such as `; charset=utf-8` are ignored. A request with no `Content-Type` (a GET) is rejected too, so use it on POST-only routes |

`rateLimit` and `geoRateLimits` need Redis to count across requests (see [Configuration](configuration.md#redis)).

### Who can call the route

| Option | Example | What it does |
| --- | --- | --- |
| `ipWhitelist` | `['203.0.113.7', '10.0.0.0/8']` | Only these IPs or CIDR ranges, everyone else gets 403. When set, `ipBlacklist` and the global IP lists are ignored on this route |
| `ipBlacklist` | `['198.51.100.0/24']` | These IPs or ranges get 403 |
| `blockedCountries` | `['CN', 'RU']` | Uppercase ISO codes get 403. Needs `ipinfo`, without it nobody is blocked |
| `whitelistCountries` | `['IT', 'DE']` | Only these countries, everyone else gets 403. Needs `ipinfo`, without it every request is blocked |
| `blockCloudProviders` | `['AWS', 'GCP', 'Azure:!eastus']` | Requests from the published ranges of `AWS`, `GCP`, `Azure`, `DigitalOcean`, `Linode` or `Vultr` get 403. `Provider:!region` leaves one region out. Names are case sensitive |
| `blockedUserAgents` | `['sqlmap', 'python-requests']` | A `User-Agent` that matches one of these regular expressions (case insensitive, anywhere in the text) gets 403. Adds to the global list |
| `timeRestrictions` | `['start' => '09:00', 'end' => '18:00', 'timezone' => 'Europe/Rome']` | Outside the window, 403. 24 hour `HH:MM` with leading zeros, timezone optional (default UTC). A start after the end is an overnight window |
| `requireReferrer` | `['example.com']` | A request without `Referer` or with a `Referer` from another domain gets 403. Subdomains count |
| `requireHttps` | `true` | A plain HTTP request gets a 301 to the https URL. Behind a TLS proxy set `trustXForwardedProto` and `trustedProxies` at the `guardCore` level, or every request looks like HTTP |

Client IPs come from `REMOTE_ADDR` unless `trustedProxies` is set (see [Configuration](configuration.md#trusted-proxies-and-ipaddressserverparam)).

### What the request must carry

| Option | Example | What it does |
| --- | --- | --- |
| `requiredHeaders` | `['X-Requested-With' => 'XMLHttpRequest', 'X-Client' => 'required']` | A map of header to value. A missing header or a different value gets 400. The value `required` means any value. A plain list (`['X-Client']`) does not work |
| `authorizationHeaderRequired` | `'bearer'` | The `Authorization` header must be there with that scheme (`bearer` or `basic`), else 401. It checks that a token is sent, not that it is valid: EspoCRM does that |
| `honeypotFields` | `['website']` | A POST, PUT or PATCH with a JSON or form body where one of these top-level fields is filled gets 403. The field has to exist in your form and stay empty for people |

`authRequired`, `apiKeyRequired` and `apiKeyHeader` are accepted but do not work in this extension: they call a verifier function, which a config file cannot hold, so every request on the route fails with 401. Use `authorizationHeaderRequired`.

### Attack screening on the route

| Option | Example | What it does |
| --- | --- | --- |
| `enableSuspiciousDetection` | `false` | Turns attack screening off for this route. Default `true` |
| `detectionScanBody` | `false` | Screens the path, query and headers but not the body. The default `POST /api/v1/Attachment/chunk/` route uses it |
| `enabledDetectionCategories` | `['sqli', 'xss']` | Only these categories on this route, `[]` for none. Names: `xss`, `sqli`, `dir_traversal`, `path_traversal`, `cmd_injection`, `file_inclusion`, `ldap`, `xml`, `ssrf`, `nosql`, `file_upload`, `template`, `http_split`, `sensitive_file`, `cms_probing`, `recon`, `proto_pollution`, `code_injection`, `deserialization`. A misspelled name is not rejected here, it just never matches |
| `excludedDetectionParams` | `['q']` | Query parameters left out of the screening. Lowercase. Replaces the global list on this route |
| `excludedDetectionBodyFields` | `['description']` | Body fields left out of the screening, at any depth of a JSON body. Lowercase. Replaces the global list on this route |
| `excludedDetectionHeaders` | `['x-forwarded-host']` | Headers still screened, but an address in them (a host or IP list) is not taken for SSRF. Adds to the built-in list |
| `excludedSemanticDetectionHeaders` | `['x-client-note']` | Headers kept out of the semantic analysis only, the pattern screening still runs. Adds to the global list |

A detected attack gets 400.

### Skipping checks

| Option | Example | What it does |
| --- | --- | --- |
| `bypassedChecks` | `['rate_limit']` | Skips engine checks on this route: `ip_ban`, `ip` (IP and country lists), `clouds`, `rate_limit`, `penetration` (attack screening), or `all` for those five. It does not skip the other options on this page |
