<?php

use Espo\Core\Container;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config\ConfigWriter;

/**
 * Once the module is gone nothing hides `guard` (Redis URL, agent API key) from the Settings API any more, so the
 * keys are removed from the config files.
 */
class AfterUninstall
{
    public function run(Container $container): void
    {
        $configWriter = $container->getByClass(InjectableFactory::class)->create(ConfigWriter::class);

        $configWriter->remove('guardEnabled');
        $configWriter->remove('guard');
        $configWriter->save();
    }
}
