<?php

namespace tests\unit\Espo\Modules\GuardCore;

use Espo\Core\Container;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Config\ConfigWriter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../src/scripts/AfterInstall.php';

class AfterInstallTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $set = [];

    /** @var string[] */
    private array $removed = [];

    /** @var string[] */
    private array $nulled = [];

    private int $saves = 0;

    public function testMovesBothOldKeysUnderGuardCoreWithEnabledFirst(): void
    {
        $guard = ['passiveMode' => false, 'routes' => ['/x/' => ['rateLimit' => 2]]];

        $this->runWith(['guardEnabled' => true, 'guard' => $guard]);

        $this->assertSame(['guardCore' => ['enabled' => true, ...$guard]], $this->set);
        $this->assertSame(['enabled', 'passiveMode', 'routes'], array_keys($this->set['guardCore']));
        $this->assertSame(['guardEnabled', 'guard'], $this->removed);
        $this->assertSame(['guardEnabled', 'guard'], $this->nulled);
        $this->assertSame(1, $this->saves);
    }

    public function testGuardEnabledAloneBecomesEnabledOnly(): void
    {
        $this->runWith(['guardEnabled' => true]);

        $this->assertSame(['guardCore' => ['enabled' => true]], $this->set);
        $this->assertSame(1, $this->saves);
    }

    public function testDisabledGuardEnabledKeepsTheGuardMapDisabled(): void
    {
        $this->runWith(['guardEnabled' => false, 'guard' => ['passiveMode' => false]]);

        $this->assertSame(['guardCore' => ['enabled' => false, 'passiveMode' => false]], $this->set);
    }

    public function testGuardMapAloneBecomesDisabled(): void
    {
        $this->runWith(['guard' => ['passiveMode' => false]]);

        $this->assertSame(['guardCore' => ['enabled' => false, 'passiveMode' => false]], $this->set);
    }

    public function testGuardThatIsNotAMapChangesNothing(): void
    {
        $this->runWith(['guardEnabled' => true, 'guard' => 'nope']);

        $this->assertNothingWritten();
    }

    public function testGuardCoreAlreadySetChangesNothing(): void
    {
        $this->runWith(['guardCore' => ['enabled' => true], 'guardEnabled' => true, 'guard' => []]);

        $this->assertNothingWritten();
    }

    public function testFreshInstallWritesNothing(): void
    {
        $this->runWith([]);

        $this->assertNothingWritten();
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function oldConfigProvider(): array
    {
        return [
            'both keys' => [['guardEnabled' => true, 'guard' => ['passiveMode' => false]]],
            'guardEnabled only' => [['guardEnabled' => true]],
            'guard only' => [['guard' => ['passiveMode' => false]]],
        ];
    }

    /**
     * @param array<string, mixed> $old
     */
    #[DataProvider('oldConfigProvider')]
    public function testASecondRunChangesNothing(array $old): void
    {
        $this->runWith($old);

        $migrated = $this->set;
        $this->assertSame(1, $this->saves);

        $this->runWith($migrated);

        $this->assertSame(1, $this->saves, 'the second run must not save again');
    }

    /**
     * @param array<string, mixed> $values
     */
    private function runWith(array $values): void
    {
        $this->set = [];
        $this->removed = [];
        $this->nulled = [];

        $config = $this->createStub(Config::class);
        $config->method('get')->willReturnCallback(fn (string $name, $default = null) => $values[$name] ?? $default);

        $configWriter = $this->createStub(ConfigWriter::class);
        $configWriter->method('set')->willReturnCallback(function (string $name, $value): void {
            if ($value === null) {
                $this->nulled[] = $name;

                return;
            }

            $this->set[$name] = $value;
        });
        $configWriter->method('remove')->willReturnCallback(function (string $name): void {
            $this->removed[] = $name;
        });
        $configWriter->method('save')->willReturnCallback(function (): void {
            $this->saves++;
        });

        $factory = $this->createStub(InjectableFactory::class);
        $factory->method('create')->willReturn($configWriter);

        $container = $this->createStub(Container::class);
        $container->method('getByClass')->willReturnCallback(
            fn (string $class) => $class === Config::class ? $config : $factory
        );

        (new \AfterInstall())->run($container);
    }

    private function assertNothingWritten(): void
    {
        $this->assertSame([], $this->set);
        $this->assertSame([], $this->removed);
        $this->assertSame([], $this->nulled);
        $this->assertSame(0, $this->saves);
    }
}
