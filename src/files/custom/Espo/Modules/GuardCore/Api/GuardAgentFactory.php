<?php

namespace Espo\Modules\GuardCore\Api;

use RuntimeException;

/**
 * Creates the optional Guard Core telemetry agent (the `rennf93/guard-agent-php` package, listed under `suggest`).
 * It is resolved by name because the package is not a dependency.
 */
class GuardAgentFactory
{
    private const AGENT_CLASS = 'RenzoFranceschini\\GuardAgent\\GuardAgent';

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

        register_shutdown_function([$this, 'flushAndStop'], $agent);

        return $agent;
    }

    /**
     * Runs at the end of the request so the buffered events reach the agent backend.
     */
    public function flushAndStop(object $agent): void
    {
        // The agent class is resolved by name, so its methods are not known statically.
        if (!method_exists($agent, 'flushBuffer') || !method_exists($agent, 'stop')) {
            throw new RuntimeException('Guard agent ' . $agent::class . ' has no flushBuffer() or stop() method.');
        }

        $agent->flushBuffer();
        $agent->stop();
    }
}
