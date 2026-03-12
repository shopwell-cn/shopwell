<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\Content\Test\Seo\Twig\LastLetterBigTwigFilter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(LastLetterBigTwigFilter::class)
        ->tag('shopwell.seo_url.twig.extension');
};
