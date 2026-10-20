<?php

namespace tests\unit\Espo\Modules\GuardCore\Api;

use Espo\Core\Utils\Config;
use Espo\Core\Utils\Log;
use Espo\Modules\GuardCore\Api\DefaultPolicy;
use Espo\Modules\GuardCore\Api\GuardAgentFactory;
use Espo\Modules\GuardCore\Api\GuardMiddlewareBuilder;
use Espo\Modules\GuardCore\Api\RouteConfigBuilder;
use Espo\Modules\GuardCore\Api\SecurityConfigBuilder;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use RenzoFranceschini\GuardAgent\GuardAgent;
use RuntimeException;

/**
 * The first test needs the agent class to be absent, so the tests that load the stub depend on it: once the stub is
 * loaded it stays loaded for the rest of the process.
 */
class GuardAgentFactoryTest extends TestCase
{
    public function testMissingPackageFailsWithInstallInstructions(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('composer require rennf93/guard-agent-php');

        (new GuardAgentFactory())->create(['apiKey' => 'k']);
    }

    #[Depends('testMissingPackageFailsWithInstallInstructions')]
    public function testCreatesAndStartsTheAgentWithTheGivenSettings(): void
    {
        require_once __DIR__ . '/Fixtures/GuardAgentStub.php';

        $agent = (new GuardAgentFactory())->create(['apiKey' => 'key-1']);

        $this->assertInstanceOf(GuardAgent::class, $agent);
        $this->assertSame(['apiKey' => 'key-1'], $agent->settings);
        $this->assertSame(['start'], $agent->calls);
    }

    #[Depends('testMissingPackageFailsWithInstallInstructions')]
    public function testFlushAndStopDrainsTheBufferThenStopsTheAgent(): void
    {
        require_once __DIR__ . '/Fixtures/GuardAgentStub.php';

        $agent = new GuardAgent([]);

        (new GuardAgentFactory())->flushAndStop($agent);

        $this->assertSame(['flushBuffer', 'stop'], $agent->calls);
    }

    public function testFlushAndStopFailsLoudlyOnAnAgentWithoutTheLifecycleMethods(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has no flushBuffer() or stop() method');

        (new GuardAgentFactory())->flushAndStop(new \stdClass());
    }

    #[Depends('testMissingPackageFailsWithInstallInstructions')]
    public function testBuilderEnablesTheAgentWhenAgentSettingsAreGiven(): void
    {
        require_once __DIR__ . '/Fixtures/GuardAgentStub.php';

        $builder = new GuardMiddlewareBuilder(
            $this->createStub(Config::class),
            new Log('test', [new TestHandler()]),
            new SecurityConfigBuilder(),
            new RouteConfigBuilder(),
            new GuardAgentFactory(),
        );

        $settings = (new DefaultPolicy())->merge(['agent' => ['apiKey' => 'key-1']]);

        $middleware = $builder->build($settings);

        $this->assertNotNull($middleware->agentStats());
    }
}
