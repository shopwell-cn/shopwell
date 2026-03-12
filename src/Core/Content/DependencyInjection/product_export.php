<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Content\Product\ProductDefinition;
use Shopwell\Core\Content\ProductExport\Api\ProductExportController;
use Shopwell\Core\Content\ProductExport\Command\ProductExportGenerateCommand;
use Shopwell\Core\Content\ProductExport\DataAbstractionLayer\ProductExportExceptionHandler;
use Shopwell\Core\Content\ProductExport\EventListener\ProductExportEventListener;
use Shopwell\Core\Content\ProductExport\ProductExportDefinition;
use Shopwell\Core\Content\ProductExport\SalesChannel\ExportController;
use Shopwell\Core\Content\ProductExport\ScheduledTask\ProductExportGenerateTask;
use Shopwell\Core\Content\ProductExport\ScheduledTask\ProductExportGenerateTaskHandler;
use Shopwell\Core\Content\ProductExport\ScheduledTask\ProductExportPartialGenerationHandler;
use Shopwell\Core\Content\ProductExport\Service\ProductExporter;
use Shopwell\Core\Content\ProductExport\Service\ProductExportFileHandler;
use Shopwell\Core\Content\ProductExport\Service\ProductExportGenerator;
use Shopwell\Core\Content\ProductExport\Service\ProductExportRenderer;
use Shopwell\Core\Content\ProductExport\Service\ProductExportValidator;
use Shopwell\Core\Content\ProductExport\Validator\XmlValidator;
use Shopwell\Core\Content\ProductStream\Service\ProductStreamBuilder;
use Shopwell\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopwell\Core\Framework\Adapter\Translation\Translator;
use Shopwell\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use Shopwell\Core\Framework\Adapter\Twig\TwigVariableParserFactory;
use Shopwell\Core\System\Locale\LanguageLocaleCodeProvider;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextService;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('product_export.directory', 'export');
    $parameters->set('product_export.read_buffer_size', 100);
    $parameters->set('product_export.stale_min_seconds', 300);
    $parameters->set('product_export.stale_interval_factor', '2.0');

    $services->set(ProductExportDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(ProductExportRenderer::class)
        ->args([
            service(StringTemplateRenderer::class),
            service('event_dispatcher'),
        ]);

    $services->set(ProductExporter::class)
        ->public()
        ->args([
            service('product_export.repository'),
            service(ProductExportGenerator::class),
            service('event_dispatcher'),
            service(ProductExportFileHandler::class),
        ]);

    $services->set(ProductExportFileHandler::class)
        ->args([
            service('shopwell.filesystem.private'),
            '%product_export.directory%',
        ]);

    $services->set(ProductExportGenerator::class)
        ->public()
        ->args([
            service(ProductStreamBuilder::class),
            service('sales_channel.product.repository'),
            service(ProductExportRenderer::class),
            service('event_dispatcher'),
            service(ProductExportValidator::class),
            service(SalesChannelContextService::class),
            service(Translator::class),
            service(SalesChannelContextPersister::class),
            service(Connection::class),
            '%product_export.read_buffer_size%',
            service(SeoUrlPlaceholderHandlerInterface::class),
            service('twig'),
            service(ProductDefinition::class),
            service(LanguageLocaleCodeProvider::class),
            service(TwigVariableParserFactory::class),
        ]);

    $services->set(ProductExportGenerateCommand::class)
        ->args([
            service(SalesChannelContextFactory::class),
            service(ProductExporter::class),
        ])
        ->tag('console.command');

    $services->set(ProductExportGenerateTask::class)
        ->tag('shopwell.scheduled.task');

    $services->set(ProductExportGenerateTaskHandler::class)
        ->args([
            service('scheduled_task.repository'),
            service('logger'),
            service(Connection::class),
            service('messenger.default_bus'),
            '%product_export.stale_min_seconds%',
            '%product_export.stale_interval_factor%',
        ])
        ->tag('messenger.message_handler');

    $services->set(ProductExportPartialGenerationHandler::class)
        ->args([
            service(ProductExportGenerator::class),
            service(SalesChannelContextFactory::class),
            service('product_export.repository'),
            service(ProductExportFileHandler::class),
            service('messenger.default_bus'),
            service(ProductExportRenderer::class),
            service(Translator::class),
            service(SalesChannelContextService::class),
            service(SalesChannelContextPersister::class),
            service(Connection::class),
            '%product_export.read_buffer_size%',
            service(LanguageLocaleCodeProvider::class),
        ])
        ->tag('messenger.message_handler');

    $services->set(ProductExportController::class)
        ->public()
        ->args([
            service('sales_channel_domain.repository'),
            service('sales_channel.repository'),
            service(ProductExportGenerator::class),
            service('event_dispatcher'),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(ProductExportValidator::class)
        ->args([tagged_iterator('shopwell.product_export.validator')]);

    $services->set(XmlValidator::class)
        ->tag('shopwell.product_export.validator');

    $services->set(ProductExportExceptionHandler::class)
        ->tag('shopwell.dal.exception_handler');

    $services->set(ProductExportEventListener::class)
        ->args([
            service('product_export.repository'),
            service(ProductExportFileHandler::class),
            service('shopwell.filesystem.private'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(ExportController::class)
        ->public()
        ->args([
            service(ProductExporter::class),
            service(ProductExportFileHandler::class),
            service('shopwell.filesystem.private'),
            service('event_dispatcher'),
            service('product_export.repository'),
            service(SalesChannelContextFactory::class),
        ]);
};
