<?php

namespace tests\unit\Espo\Modules\GuardCore;

use PHPUnit\Framework\Attributes\DataProvider;

require_once __DIR__ . '/../../../../../src/scripts/AfterInstall.php';

class AfterInstallConfigFilesTest extends RealConfigFilesTestCase
{
    private const OLD_KEYS = ['guardEnabled', 'guard'];

    private const GUARD = ['passiveMode' => false, 'routes' => ['/x/' => ['rateLimit' => 2]]];

    private const MIGRATED = ['enabled' => true, 'passiveMode' => false, 'routes' => ['/x/' => ['rateLimit' => 2]]];

    public function testOldKeysInConfigPhpOnlyMoveIntoConfigInternal(): void
    {
        $this->writeConfigFile('config.php', ['siteUrl' => 'https://crm.test', 'guardEnabled' => true, 'guard' => self::GUARD]);
        $this->writeConfigFile('config-internal.php', ['passwordSalt' => 'salt']);

        $this->migrate();

        $this->assertMigrated();
        $this->assertSame('https://crm.test', $this->readConfigFile('config.php')['siteUrl']);
        $this->assertSame('salt', $this->readConfigFile('config-internal.php')['passwordSalt']);
    }

    public function testOldKeysInConfigInternalOnlyAreReplacedByGuardCore(): void
    {
        $this->writeConfigFile('config.php', ['siteUrl' => 'https://crm.test']);
        $this->writeConfigFile('config-internal.php', ['guardEnabled' => true, 'guard' => self::GUARD]);

        $this->migrate();

        $this->assertMigrated();
    }

    public function testOldKeysSplitAcrossBothFilesAreMigrated(): void
    {
        $this->writeConfigFile('config.php', ['guardEnabled' => true]);
        $this->writeConfigFile('config-internal.php', ['guard' => self::GUARD]);

        $this->migrate();

        $this->assertMigrated();
    }

    public function testOldKeysInConfigPhpAndConfigInternalAtOnceAreMigrated(): void
    {
        $this->writeConfigFile('config.php', ['guardEnabled' => true, 'guard' => ['passiveMode' => true]]);
        $this->writeConfigFile('config-internal.php', ['guardEnabled' => true, 'guard' => self::GUARD]);

        $this->migrate();

        $this->assertMigrated();
    }

    /**
     * @return array<string, array{string, array<string, mixed>}>
     */
    public static function overrideProvider(): array
    {
        return [
            'guardEnabled in config-override' => ['config-override.php', ['guardEnabled' => true]],
            'guard in config-override' => ['config-override.php', ['guard' => self::GUARD]],
            'guardEnabled in config-internal-override' => ['config-internal-override.php', ['guardEnabled' => true]],
            'guard in config-internal-override' => ['config-internal-override.php', ['guard' => self::GUARD]],
        ];
    }

    /**
     * @param array<string, mixed> $override
     */
    #[DataProvider('overrideProvider')]
    public function testOldKeysInAnOverrideFileChangeNothing(string $overrideFile, array $override): void
    {
        $this->writeConfigFile('config.php', ['siteUrl' => 'https://crm.test', 'guardEnabled' => false]);
        $this->writeConfigFile('config-internal.php', ['passwordSalt' => 'salt']);
        $this->writeConfigFile($overrideFile, $override);
        $before = $this->allConfigContents();

        $this->migrate();

        $this->assertSame($before, $this->allConfigContents());
    }

    public function testAnOverrideFileWithoutOldKeysDoesNotBlockTheMigration(): void
    {
        $this->writeConfigFile('config.php', ['guardEnabled' => true, 'guard' => self::GUARD]);
        $this->writeConfigFile('config-internal.php', []);
        $this->writeConfigFile('config-override.php', ['siteUrl' => 'https://override.test']);

        $this->migrate();

        $this->assertMigrated();
    }

    public function testFreshInstallWritesNeitherFile(): void
    {
        $this->writeConfigFile('config.php', ['siteUrl' => 'https://crm.test']);
        $this->writeConfigFile('config-internal.php', ['passwordSalt' => 'salt']);
        $before = $this->allConfigContents();

        $this->migrate();

        $this->assertSame($before, $this->allConfigContents());
    }

    public function testASecondRunChangesNothing(): void
    {
        $this->writeConfigFile('config.php', ['guardEnabled' => true, 'guard' => self::GUARD]);
        $this->writeConfigFile('config-internal.php', []);

        $this->migrate();
        $afterFirstRun = $this->allConfigContents();

        $this->migrate();

        $this->assertSame($afterFirstRun, $this->allConfigContents());
    }

    private function migrate(): void
    {
        (new \AfterInstall())->run($this->container());
    }

    /**
     * @return array<string, string>
     */
    private function allConfigContents(): array
    {
        $contents = [];

        foreach (['config.php', 'config-internal.php', 'config-override.php', 'config-internal-override.php'] as $file) {
            $contents[$file] = $this->configFileExists($file) ? $this->configFileContents($file) : '';
        }

        return $contents;
    }

    private function assertMigrated(): void
    {
        $this->assertSame(self::MIGRATED, $this->readConfigFile('config-internal.php')['guardCore']);
        $this->assertSame(['enabled', 'passiveMode', 'routes'], array_keys($this->readConfigFile('config-internal.php')['guardCore']));
        $this->assertArrayNotHasKey('guardCore', $this->readConfigFile('config.php'));

        foreach (['config.php', 'config-internal.php'] as $file) {
            foreach (self::OLD_KEYS as $oldKey) {
                $this->assertArrayNotHasKey($oldKey, $this->readConfigFile($file), "$oldKey left in $file");
            }
        }
    }
}
