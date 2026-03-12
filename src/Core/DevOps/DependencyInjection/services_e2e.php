<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Shopwell\Core\DevOps\System\Command\SystemDumpDatabaseCommand;
use Shopwell\Core\DevOps\System\Command\SystemRestoreDatabaseCommand;
use Symfony\Component\HttpClient\MockHttpClient;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire();

    $services->set(SystemDumpDatabaseCommand::class)
        ->args([
            '%kernel.project_dir%/var/dumps',
            service(Connection::class),
        ])
        ->tag('console.command', ['command' => 'e2e:dump-db']);

    $services->set(SystemRestoreDatabaseCommand::class)
        ->args([
            '%kernel.project_dir%/var/dumps',
            service(Connection::class),
        ])
        ->tag('console.command', ['command' => 'e2e:restore-db'])
        ->tag('console.command', ['command' => 'e2e:cleanup']);

    $services->set('shopwell.usage_data.gateway.client', MockHttpClient::class)
        ->public();
};
