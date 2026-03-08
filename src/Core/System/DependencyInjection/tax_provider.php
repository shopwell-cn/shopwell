<?php declare(strict_types=1);

use Shopwell\Core\System\TaxProvider\Aggregate\TaxProviderTranslation\TaxProviderTranslationDefinition;
use Shopwell\Core\System\TaxProvider\TaxProviderDefinition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(TaxProviderDefinition::class)->tag('shopwell.entity.definition');
    $services->set(TaxProviderTranslationDefinition::class)->tag('shopwell.entity.definition');

};
