<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\System\DeliveryTime\Aggregate\DeliveryTimeTranslation\DeliveryTimeTranslationDefinition;
use Shopwell\Core\System\DeliveryTime\DeliveryTimeDefinition;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set(DeliveryTimeDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(DeliveryTimeTranslationDefinition::class)
        ->tag('shopwell.entity.definition');
};
