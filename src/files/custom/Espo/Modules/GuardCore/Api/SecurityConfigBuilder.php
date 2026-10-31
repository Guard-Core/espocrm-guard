<?php

namespace Espo\Modules\GuardCore\Api;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use RenzoFranceschini\GuardCore\GeoIp\CountryResolver;
use RenzoFranceschini\GuardCore\Config\SecurityConfig;

/**
 * Maps the `guardCore` settings onto the named arguments of the engine's `SecurityConfig`, so every engine option is
 * available from the config. An unknown key fails loudly.
 */
class SecurityConfigBuilder
{
    /** Settings that are not engine options, consumed by other builders. */
    private const NON_ENGINE_KEYS = ['routes', 'agent', 'ipinfo'];

    /** Options that take a closure or an object, which a config file cannot express. */
    private const NOT_CONFIGURABLE = [
        'onBlock',
        'customRequestCheck',
        'authVerifier',
        'customResponseModifier',
        'geoIpHandler',
    ];

    /**
     * @param array<string, mixed> $settings
     */
    public function build(
        array $settings,
        ?CountryResolver $geoIpHandler = null,
        ?Closure $onBlock = null,
    ): SecurityConfig
    {
        $options = array_diff_key($settings, array_flip(self::NON_ENGINE_KEYS));

        $unknownKeys = array_diff(array_keys($options), $this->getConfigurableOptionNames());

        if ($unknownKeys !== []) {
            throw new InvalidArgumentException(
                "Unknown guard config key(s): " . implode(', ', $unknownKeys) . "."
            );
        }

        return new SecurityConfig(...$options, geoIpHandler: $geoIpHandler, onBlock: $onBlock);
    }

    /**
     * @return string[]
     */
    private function getConfigurableOptionNames(): array
    {
        $constructor = (new ReflectionClass(SecurityConfig::class))->getConstructor();

        $names = array_map(fn ($parameter) => $parameter->getName(), $constructor?->getParameters() ?? []);

        return array_values(array_diff($names, self::NOT_CONFIGURABLE));
    }
}
