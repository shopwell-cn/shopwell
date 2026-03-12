<?php declare(strict_types=1);
/**
 * @codeCoverageIgnore - DI wiring only
 */
use Shopwell\Core\System\Unit\Aggregate\UnitTranslation\UnitTranslationDefinition;
use Shopwell\Core\System\Unit\UnitDefinition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->set(UnitDefinition::class)->tag('shopwell.entity.definition');
    $services->set(UnitTranslationDefinition::class)->tag('shopwell.entity.definition');
};
