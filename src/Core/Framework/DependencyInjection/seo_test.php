<?php declare(strict_types=1);

use Shopwell\Core\Content\Test\Seo\Twig\LastLetterBigTwigFilter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(LastLetterBigTwigFilter::class)
        ->tag('shopwell.seo_url.twig.extension');
};
