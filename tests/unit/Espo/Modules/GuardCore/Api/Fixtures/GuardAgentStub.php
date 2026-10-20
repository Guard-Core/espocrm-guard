<?php

namespace RenzoFranceschini\GuardAgent;

/**
 * Stands in for rennf93/guard-agent-php, which the extension does not depend on. Loaded only by tests that run in a
 * separate process, so the class never exists in the process that checks the missing-package error.
 */
class GuardAgent
{
    /** @var string[] */
    public array $calls = [];

    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(public array $settings) {}

    public function start(): void
    {
        $this->calls[] = 'start';
    }

    public function flushBuffer(): void
    {
        $this->calls[] = 'flushBuffer';
    }

    public function stop(): void
    {
        $this->calls[] = 'stop';
    }

    public function sendEvent(object $event): void
    {
        $this->calls[] = 'sendEvent';
    }

    public function sendMetric(object $metric): void
    {
        $this->calls[] = 'sendMetric';
    }
}
