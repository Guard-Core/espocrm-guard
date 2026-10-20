<?php

// Redis holds the rate limit, behavior and ban state so it survives across requests and workers. nginx is the
// reverse proxy in front of EspoCRM, so only its fixed address is trusted to say who the client is (X-Forwarded-For).
// Not the whole network: the Docker gateway that host clients come from is in it, and the engine never bans a trusted
// proxy address. Passive mode is off: attacks are blocked.
return [
    'guardEnabled' => true,
    'guard' => [
        'passiveMode' => false,
        'enableRedis' => true,
        'redisUrl' => 'redis://redis:6379',
        'redisPrefix' => 'espocrm_guard_example:',
        'trustedProxies' => ['172.29.60.10'],
        'routes' => [
            // The default is 10 per minute. This example allows 3 so the limit is easy to see.
            'POST /api/v1/LeadCapture/' => ['rateLimit' => 3, 'rateLimitWindow' => 60],
            // The default ban after 10 responses with status 401 in 5 minutes, lowered to 3 for the example.
            'GET /api/v1/App/user' => ['behaviorRules' => [[
                'rule_type' => 'return_pattern',
                'pattern' => 'status:401',
                'threshold' => 3,
                'window' => 300,
                'action' => 'ban',
                'ban_duration' => 3600,
            ]]],
        ],

        // The telemetry agent is optional and is not bundled. After `composer require rennf93/guard-agent-php` in
        // the EspoCRM root, uncomment this map (see docs/telemetry-agent.md):
        // 'agent' => ['apiKey' => 'your-project-api-key'],
    ],
];
