<?php declare(strict_types=1);

use Shopwell\Core\System\Language\LanguageLoader;
use Shopwell\Core\System\Locale\Aggregate\LocaleTranslation\LocaleTranslationDefinition;
use Shopwell\Core\System\Locale\LanguageLocaleCodeProvider;
use Shopwell\Core\System\Locale\LocaleDefinition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(LocaleDefinition::class)->tag('shopwell.entity.definition');
    $services->set(LocaleTranslationDefinition::class)->tag('shopwell.entity.definition');

    $services->set(LanguageLocaleCodeProvider::class)
        ->args([
            service(LanguageLoader::class),
        ])
        ->tag('kernel.reset', ['method' => 'reset']);
};
