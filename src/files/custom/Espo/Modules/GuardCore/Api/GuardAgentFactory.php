<?php

namespace Espo\Modules\GuardCore\Api;

use RuntimeException;

/**
 * Creates the optional Guard Core telemetry agent (the `rennf93/guard-agent-php` package, listed under `suggest`).
 * It is resolved by name because the package is not a dependency.
 */
class GuardAgentFactory
{
    private const string AGENT_CLASS = 'RenzoFranceschini\\GuardAgent\\GuardAgent';

    /**
     * @param array<string, mixed> $settings
     */
    public function create(array $settings): object
    {
        $className = self::AGENT_CLASS;

        if (!class_exists($className)) {
            throw new RuntimeException(
                "Guard config 'agent' requires the rennf93/guard-agent-php package. Run: composer require rennf93/guard-agent-php"
            );
        }

        $agent = new $className($settings);

        $agent->start();

        register_shutdown_function(function () use ($agent): void {
            $agent->flushBuffer();
            $agent->stop();
        });

        return $agent;
    }
}
