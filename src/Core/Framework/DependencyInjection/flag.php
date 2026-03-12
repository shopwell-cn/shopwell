<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\Framework\Adapter\Storage\AbstractKeyValueStorage;
use Shopwell\Core\Framework\Feature\FeatureFlagRegistry;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(FeatureFlagRegistry::class)
        ->public()
        ->args([
            service(AbstractKeyValueStorage::class),
            service('event_dispatcher'),
            '%shopwell.feature.flags%',
            '%shopwell.feature_toggle.enable%',
        ]);
};
