<?php

namespace Espo\Modules\GuardCore\Api;

/**
 * The policy applied when `guardEnabled` is true: passive mode, screening on, and route policies for the
 * endpoints that are internet facing or credential related. Operator config overrides it key by key.
 *
 * Redis is off by default, and without it route rate limits, behavior rules and auto-bans keep state per request only,
 * so under PHP-FPM they never trigger across requests until `enableRedis` is set.
 */
class DefaultPolicy
{
    private const API_PREFIX = '/api/v1/';

    /**
     * @param array<string, mixed> $operatorSettings
     * @return array<string, mixed>
     */
    public function merge(array $operatorSettings): array
    {
        $settings = array_replace($this->getSettings(), $operatorSettings);

        $operatorRoutes = $operatorSettings['routes'] ?? [];

        if (!is_array($operatorRoutes)) {
            throw new \InvalidArgumentException("Guard config 'routes' must be a map of route key to options.");
        }

        // A null route entry switches a default route off.
        $settings['routes'] = array_filter(
            array_replace($this->getRoutes(), $operatorRoutes),
            fn ($route) => $route !== null
        );

        return $settings;
    }

    /**
     * @return array<string, mixed>
     */
    private function getSettings(): array
    {
        return [
            'passiveMode' => true,
            'enableRedis' => false,
            // Route limits only apply while rate limiting is on, so the global per-IP limit is set out of reach.
            'enableRateLimiting' => true,
            'rateLimit' => 10000,
            'rateLimitWindow' => 60,
            'enablePenetrationDetection' => true,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getRoutes(): array
    {
        return [
            'GET ' . self::API_PREFIX . 'App/user' => ['behaviorRules' => [$this->getRepeatedUnauthorizedRule()]],
            self::API_PREFIX => ['behaviorRules' => [$this->getRepeatedUnauthorizedRule()]],
            'POST ' . self::API_PREFIX . 'Attachment/chunk/' => [
                'detectionScanBody' => false,
                'behaviorRules' => [$this->getRepeatedUnauthorizedRule()],
            ],
            'POST ' . self::API_PREFIX . 'LeadCapture/' => ['rateLimit' => 10, 'rateLimitWindow' => 60],
            'POST ' . self::API_PREFIX . 'User/passwordChangeRequest' => ['rateLimit' => 5, 'rateLimitWindow' => 300],
            'POST ' . self::API_PREFIX . 'User/changePasswordByRequest' => ['rateLimit' => 5, 'rateLimitWindow' => 300],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getRepeatedUnauthorizedRule(): array
    {
        return [
            'rule_type' => 'return_pattern',
            'pattern' => 'status:401',
            'threshold' => 10,
            'window' => 300,
            'action' => 'ban',
            'ban_duration' => 3600,
        ];
    }
}
