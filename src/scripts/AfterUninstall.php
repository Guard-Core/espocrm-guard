<?php

use Espo\Core\Container;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config\ConfigWriter;

/**
 * Once the module is gone nothing hides `guardCore` (Redis URL, agent API key) from the Settings API any more, so the
 * keys (and the old `guardEnabled` and `guard`) are removed from the config files.
 */
class AfterUninstall
{
    public function run(Container $container): void
    {
        $configWriter = $container->getByClass(InjectableFactory::class)->create(ConfigWriter::class);

        foreach (['guardCore', 'guardEnabled', 'guard'] as $key) {
            // Before EspoCRM 9.3 `remove` drops a level system param from config-internal.php only, so a key kept in
            // config.php survived and, with this module gone, lost its system level (ConfigWriter::save at 9.0.0,
            // lines 143-153). `set` moves a system param out of config.php into config-internal.php (lines 130-141),
            // and `remove` then drops it there. From 9.3 `remove` unsets the key from every file (lines 185-197).
            $configWriter->set($key, null);
            $configWriter->remove($key);
        }

        $configWriter->save();
    }
}
