<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\Framework\RateLimiter\RateLimiter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(RateLimiter::class);
};
