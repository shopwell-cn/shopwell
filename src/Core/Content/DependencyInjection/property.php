<?php declare(strict_types=1);

use Shopwell\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopwell\Core\Content\Property\Aggregate\PropertyGroupOptionTranslation\PropertyGroupOptionTranslationDefinition;
use Shopwell\Core\Content\Property\Aggregate\PropertyGroupTranslation\PropertyGroupTranslationDefinition;
use Shopwell\Core\Content\Property\PropertyGroupDefinition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(PropertyGroupDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(PropertyGroupOptionDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(PropertyGroupOptionTranslationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(PropertyGroupTranslationDefinition::class)
        ->tag('shopwell.entity.definition');
};
