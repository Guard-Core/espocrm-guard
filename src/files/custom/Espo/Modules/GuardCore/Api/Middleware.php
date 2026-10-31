<?php

namespace Espo\Modules\GuardCore\Api;

use Espo\Core\Utils\Config;
use Espo\Core\Utils\Log;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use RenzoFranceschini\GuardCorePsr15\GuardMiddleware;

/**
 * Opt-in application layer security (Guard Core). Does nothing, and builds nothing, unless `guardCore.enabled` (or
 * the deprecated `guardEnabled`) is true.
 */
class Middleware implements MiddlewareInterface
{
    private ?GuardMiddleware $guard = null;

    public function __construct(
        private Config $config,
        private DefaultPolicy $defaultPolicy,
        private GuardMiddlewareBuilder $builder,
        private Log $log,
    ) {}

    public function process(ServerRequest $request, RequestHandler $handler): Response
    {
        $settings = $this->resolveOperatorSettings();

        if ($settings === null) {
            return $handler->handle($request);
        }

        $this->guard ??= $this->builder->build($this->defaultPolicy->merge($settings));

        return $this->guard->process($request, $handler);
    }

    /**
     * @return array<string, mixed>|null Null when the guard is off.
     */
    private function resolveOperatorSettings(): ?array
    {
        $guardCore = $this->config->get('guardCore');

        if ($guardCore === null) {
            return $this->resolveLegacySettings();
        }

        if (!is_array($guardCore)) {
            throw new \InvalidArgumentException("Config parameter 'guardCore' must be a map of settings.");
        }

        $enabled = $guardCore['enabled'] ?? false;

        if (!is_bool($enabled)) {
            throw new \InvalidArgumentException("Config parameter 'guardCore.enabled' must be true or false.");
        }

        unset($guardCore['enabled']);

        $hasLegacyKeys = $this->config->get('guardEnabled') !== null || $this->config->get('guard') !== null;

        if (($enabled || $this->config->get('guardEnabled')) && $hasLegacyKeys) {
            throw new \InvalidArgumentException(
                "Guard settings are split between 'guardCore' and the old 'guardEnabled'/'guard'. "
                . "Move everything under 'guardCore' and remove the old keys."
            );
        }

        return $enabled ? $guardCore : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveLegacySettings(): ?array
    {
        if (!$this->config->get('guardEnabled')) {
            return null;
        }

        $settings = $this->config->get('guard') ?? [];

        if (!is_array($settings)) {
            throw new \InvalidArgumentException("Config parameter 'guard' must be a map of settings.");
        }

        if ($this->guard === null) {
            $this->log->warning(
                "Config parameters 'guardEnabled' and 'guard' are deprecated. "
                . "Move them under 'guardCore' (see docs/configuration.md)."
            );
        }

        return $settings;
    }
}
