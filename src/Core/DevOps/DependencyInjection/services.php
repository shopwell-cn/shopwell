<?php declare(strict_types=1);

use Shopwell\Core\DevOps\Docs\App\DocsAppEventCommand;
use Shopwell\Core\DevOps\Docs\Script\HooksReferenceGenerator;
use Shopwell\Core\DevOps\Docs\Script\ScriptReferenceGeneratorCommand;
use Shopwell\Core\DevOps\Docs\Script\ServiceReferenceGenerator;
use Shopwell\Core\DevOps\System\Command\OpenApiValidationCommand;
use Shopwell\Core\DevOps\System\Command\SyncComposerVersionCommand;
use Shopwell\Core\Framework\Api\ApiDefinition\DefinitionService;
use Shopwell\Core\Framework\Event\BusinessEventCollector;
use Shopwell\Core\Framework\Webhook\Hookable\HookableEventCollector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire();

    $services->set(SyncComposerVersionCommand::class)
        ->args([
            '%kernel.project_dir%',
            service(Filesystem::class),
        ])
        ->tag('console.command');

    $services->set(DocsAppEventCommand::class)
        ->args([
            service(BusinessEventCollector::class),
            service(HookableEventCollector::class),
            service('twig'),
        ])
        ->tag('console.command');

    $services->set(ScriptReferenceGeneratorCommand::class)
        ->args([tagged_iterator('shopwell.scripts_reference.generator')])
        ->tag('console.command');

    $services->set(HooksReferenceGenerator::class)
        ->args([
            service('service_container'),
            service('twig'),
            service(ServiceReferenceGenerator::class),
        ])
        ->tag('shopwell.scripts_reference.generator');

    $services->set(ServiceReferenceGenerator::class)
        ->args([
            service('twig'),
            '%kernel.project_dir%',
        ])
        ->tag('shopwell.scripts_reference.generator');

    $services->set(OpenApiValidationCommand::class)
        ->args([
            service(HttpClientInterface::class),
            service(DefinitionService::class),
        ])
        ->tag('console.command');
};
