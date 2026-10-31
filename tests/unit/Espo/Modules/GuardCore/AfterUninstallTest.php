<?php

namespace tests\unit\Espo\Modules\GuardCore;

require_once __DIR__ . '/../../../../../src/scripts/AfterUninstall.php';

class AfterUninstallTest extends RealConfigFilesTestCase
{
    public function testRemovesEveryGuardKeyFromBothConfigFilesAndKeepsTheRest(): void
    {
        $this->writeConfigFile('config.php', ['siteUrl' => 'https://crm.test', 'guardEnabled' => true, 'guard' => []]);
        $this->writeConfigFile('config-internal.php', [
            'passwordSalt' => 'salt',
            'guardCore' => ['enabled' => true, 'redisUrl' => 'redis://:secret@redis:6379'],
            'guard' => ['passiveMode' => false],
        ]);

        (new \AfterUninstall())->run($this->container());

        foreach (['config.php', 'config-internal.php'] as $file) {
            $this->assertSame([], array_intersect(['guardCore', 'guardEnabled', 'guard'], array_keys($this->readConfigFile($file))), $file);
        }

        $this->assertSame('https://crm.test', $this->readConfigFile('config.php')['siteUrl']);
        $this->assertSame('salt', $this->readConfigFile('config-internal.php')['passwordSalt']);
    }

    public function testRemovesTheKeysThatLiveInConfigPhpOnly(): void
    {
        $this->writeConfigFile('config.php', ['guardCore' => ['enabled' => true], 'guardEnabled' => true, 'guard' => []]);

        (new \AfterUninstall())->run($this->container());

        $this->assertSame([], array_intersect(['guardCore', 'guardEnabled', 'guard'], array_keys($this->readConfigFile('config.php'))));
        $this->assertSame([], array_intersect(['guardCore', 'guardEnabled', 'guard'], array_keys($this->readConfigFile('config-internal.php'))));
    }
}
