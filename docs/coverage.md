# What it covers and does not

## What it covers

The Slim API entry points: `public/api/v1/` and the portal API, through the global middleware.

## What it does not cover

- Non-Slim entry points such as `?entryPoint=...` URLs and the OAuth endpoints.
- Default route keys are literal `/api/v1/...`. A subdirectory install or portal-specific routes need your own route keys.
- A honeypot field on the public lead capture form needs a hidden input in the form view. The server side (`honeypotFields` on the route) works, the client part is not part of this extension.
- Header exclusion from logs (`logSensitiveHeaders`) is not in the default policy: guard-core-php 4.4.0 throws a TypeError for a non-empty value. The engine still redacts `Authorization`, `X-Api-Key`, `Proxy-Authorization` and `Cookie` in its logs.
- The guard is one layer. It does not replace a reverse proxy, a WAF or EspoCRM's own access control.
