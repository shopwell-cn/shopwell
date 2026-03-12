<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\Content\Cookie\SalesChannel\CookieRoute;
use Shopwell\Core\Content\Cookie\Service\CookieProvider;
use Shopwell\Storefront\Framework\Cookie\CookieProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set(CookieProvider::class)
        ->args([
            service(EventDispatcherInterface::class),
            service('translator'),
            '%session.storage.options%',
            service(CookieProviderInterface::class)->nullOnInvalid(),
        ]);

    $services->set(CookieRoute::class)
        ->public()
        ->args([service(CookieProvider::class)]);
};
