<?php declare(strict_types=1);

use Shopwell\Core\System\DeliveryTime\Aggregate\DeliveryTimeTranslation\DeliveryTimeTranslationDefinition;
use Shopwell\Core\System\DeliveryTime\DeliveryTimeDefinition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(DeliveryTimeDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(DeliveryTimeTranslationDefinition::class)
        ->tag('shopwell.entity.definition');
};
