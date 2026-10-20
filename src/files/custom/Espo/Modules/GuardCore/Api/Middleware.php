<?php

namespace Espo\Modules\GuardCore\Api;

use Espo\Core\Utils\Config;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use RenzoFranceschini\GuardCorePsr15\GuardMiddleware;

/**
 * Opt-in application layer security (Guard Core). Does nothing, and builds nothing, unless `guardEnabled` is true.
 */
class Middleware implements MiddlewareInterface
{
    private ?GuardMiddleware $guard = null;

    public function __construct(
        private Config $config,
        private DefaultPolicy $defaultPolicy,
        private GuardMiddlewareBuilder $builder,
    ) {}

    public function process(ServerRequest $request, RequestHandler $handler): Response
    {
        if (!$this->config->get('guardEnabled')) {
            return $handler->handle($request);
        }

        $this->guard ??= $this->builder->build($this->defaultPolicy->merge($this->getOperatorSettings()));

        return $this->guard->process($request, $handler);
    }

    /**
     * @return array<string, mixed>
     */
    private function getOperatorSettings(): array
    {
        $settings = $this->config->get('guard') ?? [];

        if (!is_array($settings)) {
            throw new \InvalidArgumentException("Config parameter 'guard' must be a map of settings.");
        }

        return $settings;
    }
}
