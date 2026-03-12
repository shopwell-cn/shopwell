<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\System\Integration\Aggregate\IntegrationRole\IntegrationRoleDefinition;
use Shopwell\Core\System\Integration\IntegrationDefinition;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(IntegrationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(IntegrationRoleDefinition::class)
        ->tag('shopwell.entity.definition');
};
