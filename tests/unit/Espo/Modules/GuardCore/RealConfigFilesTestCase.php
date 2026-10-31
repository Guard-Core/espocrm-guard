<?php

namespace tests\unit\Espo\Modules\GuardCore;

use Espo\Core\Container;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Config\ConfigFileManager;
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Core\Utils\Config\ConfigWriterFileManager;
use Espo\Core\Utils\Config\ConfigWriterHelper;
use Espo\Core\Utils\Config\InternalConfigHelper;
use Espo\Core\Utils\Metadata;
use PHPUnit\Framework\TestCase;

/**
 * Runs the install and uninstall scripts against EspoCRM's real Config and ConfigWriter, in a scratch EspoCRM root, and
 * lets the tests assert the contents of the config files. EspoCRM reads and writes its config through relative paths,
 * so the working directory is the scratch root while a test runs.
 */
abstract class RealConfigFilesTestCase extends TestCase
{
    private const METADATA_CONFIG = __DIR__ . '/../../../../../src/files/custom/Espo/Modules/GuardCore/Resources/metadata/app/config.json';

    private string $originalWorkingDirectory;

    private string $root;

    protected function setUp(): void
    {
        $this->originalWorkingDirectory = (string) getcwd();
        $this->root = sys_get_temp_dir() . '/espocrm-guard-' . uniqid('', true);

        mkdir($this->root . '/data', 0777, true);
        mkdir($this->root . '/application/Espo/Resources/defaults', 0777, true);
        copy(
            $this->originalWorkingDirectory . '/site/application/Espo/Resources/defaults/systemConfig.php',
            $this->root . '/application/Espo/Resources/defaults/systemConfig.php'
        );

        chdir($this->root);
    }

    protected function tearDown(): void
    {
        chdir($this->originalWorkingDirectory);

        // ConfigWriter also leaves scratch files under data/ (tmp/), so the whole scratch root goes, and only it.
        $entries = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($entries as $entry) {
            $entry->isDir() && !$entry->isLink() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }

        rmdir($this->root);
    }

    /**
     * @param array<string, mixed> $values
     */
    protected function writeConfigFile(string $name, array $values): void
    {
        file_put_contents($this->root . '/data/' . $name, '<?php return ' . var_export($values, true) . ';');
    }

    /**
     * @return array<string, mixed>
     */
    protected function readConfigFile(string $name): array
    {
        $values = include $this->root . '/data/' . $name;

        return is_array($values) ? $values : [];
    }

    protected function configFileContents(string $name): string
    {
        return (string) file_get_contents($this->root . '/data/' . $name);
    }

    protected function configFileExists(string $name): bool
    {
        return is_file($this->root . '/data/' . $name);
    }

    /**
     * What EspoCRM's container resolves: the real Config, and a real ConfigWriter whose InternalConfigHelper learns
     * the level of each key from the module's own metadata file.
     */
    protected function container(): Container
    {
        $config = new Config(new ConfigFileManager());

        $metadata = $this->createStub(Metadata::class);
        $metadata->method('get')->willReturnCallback(function ($key, $default = null) {
            $params = json_decode((string) file_get_contents(self::METADATA_CONFIG), true)['params'];

            return $params[$key[3] ?? '']['level'] ?? $default;
        });

        $configWriter = new ConfigWriter(
            $config,
            new ConfigWriterFileManager($config),
            new ConfigWriterHelper(),
            new InternalConfigHelper($config, $metadata)
        );

        $factory = $this->createStub(InjectableFactory::class);
        $factory->method('create')->willReturn($configWriter);

        $container = $this->createStub(Container::class);
        $container->method('getByClass')->willReturnCallback(
            fn (string $class) => $class === Config::class ? $config : $factory
        );

        return $container;
    }
}
