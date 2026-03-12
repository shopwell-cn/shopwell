<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\Content\RevocationRequest\SalesChannel\RevocationRequestRoute;
use Shopwell\Core\Content\RevocationRequest\Validation\RevocationRequestFormValidationFactory;
use Shopwell\Core\Framework\Validation\DataValidator;
use Shopwell\Core\System\SystemConfig\SystemConfigService;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(RevocationRequestFormValidationFactory::class)
        ->args([
            service('event_dispatcher'),
            service(SystemConfigService::class),
        ]);

    $services->set(RevocationRequestRoute::class)
        ->public()
        ->args([
            service(RevocationRequestFormValidationFactory::class),
            service(DataValidator::class),
            service('request_stack'),
            service('shopwell.rate_limiter'),
            service('event_dispatcher'),
            service(SystemConfigService::class),
            service('category.repository'),
        ]);
};
