<?php

namespace tests\unit\Espo\Modules\GuardCore;

use Espo\Core\Container;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config\ConfigWriter;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../src/scripts/AfterUninstall.php';

class AfterUninstallTest extends TestCase
{
    public function testRemovesTheGuardKeysFromTheConfigFilesAndSaves(): void
    {
        $removed = [];

        $configWriter = $this->createMock(ConfigWriter::class);
        $configWriter->expects(self::exactly(2))
            ->method('remove')
            ->willReturnCallback(function (string $name) use (&$removed): void {
                $removed[] = $name;
            });
        $configWriter->expects(self::once())->method('save');

        $factory = $this->createMock(InjectableFactory::class);
        $factory->expects(self::once())->method('create')->with(ConfigWriter::class)->willReturn($configWriter);

        $container = $this->createMock(Container::class);
        $container->expects(self::once())->method('getByClass')->with(InjectableFactory::class)->willReturn($factory);

        (new \AfterUninstall())->run($container);

        $this->assertSame(['guardEnabled', 'guard'], $removed);
    }
}
