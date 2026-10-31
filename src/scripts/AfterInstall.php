<?php

use Espo\Core\Container;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Config\ConfigWriter;

/**
 * Moves the 1.0.0 settings (`guardEnabled` and `guard`) under the single `guardCore` key. An upgrade uninstalls the
 * old version without its AfterUninstall script and then runs this one. A fresh install, a config that is already
 * migrated, a `guard` that is not a map and old keys kept in one of the two override files (which EspoCRM never
 * writes) are left alone: the middleware reports the third loudly and the old keys keep working with a deprecation
 * warning.
 */
class AfterInstall
{
    public function run(Container $container): void
    {
        $config = $container->getByClass(Config::class);

        $guardCore = $config->get('guardCore');
        $guardEnabled = $config->get('guardEnabled');
        $guard = $config->get('guard');

        if ($guardCore !== null || ($guardEnabled === null && $guard === null)) {
            return;
        }

        if ($guard !== null && !is_array($guard)) {
            return;
        }

        if ($this->overrideFileHoldsAnOldKey('data/config-override.php')
            || $this->overrideFileHoldsAnOldKey('data/config-internal-override.php')) {
            return;
        }

        $configWriter = $container->getByClass(InjectableFactory::class)->create(ConfigWriter::class);

        $configWriter->set('guardCore', ['enabled' => (bool) $guardEnabled] + ($guard ?? []));

        foreach (['guardEnabled', 'guard'] as $oldKey) {
            // Before EspoCRM 9.3 `remove` drops a level system param from config-internal.php only, so a key the admin
            // kept in config.php (the 1.0.0 docs) survived (ConfigWriter::save at 9.0.0, lines 143-153). `set` moves a
            // system param out of config.php into config-internal.php (lines 130-141), and `remove` then drops it
            // there. From 9.3 `remove` unsets the key from every file, so the pair is harmless there.
            $configWriter->set($oldKey, null);
            $configWriter->remove($oldKey);
        }

        $configWriter->save();
    }

    private function overrideFileHoldsAnOldKey(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        $data = include $path;

        return is_array($data) && (array_key_exists('guardEnabled', $data) || array_key_exists('guard', $data));
    }
}
