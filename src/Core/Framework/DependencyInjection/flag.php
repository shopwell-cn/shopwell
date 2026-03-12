<?php declare(strict_types=1);

use Shopwell\Core\Framework\Adapter\Storage\AbstractKeyValueStorage;
use Shopwell\Core\Framework\Feature\FeatureFlagRegistry;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

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
