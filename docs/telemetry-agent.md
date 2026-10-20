# Telemetry agent

The [guard-agent-php](https://packagist.org/packages/rennf93/guard-agent-php) agent is optional and is not bundled. To use it, run in the EspoCRM root directory:

```bash
composer require rennf93/guard-agent-php
```

then add an `agent` map to `guard`:

```php
'guard' => [
    'agent' => ['apiKey' => 'your-project-api-key'],
],
```

The extension loads the agent only when the package is present, and fails with a clear message when `agent` is configured without it. When an agent is configured, `enableAgent` defaults to true. The agent is started with the first guarded request and flushes its buffer when the request ends.

An EspoCRM upgrade replaces `vendor/`, so run the `composer require` command again after upgrading.

The agent API key lives in `guard`, which is hidden from the Settings API.
