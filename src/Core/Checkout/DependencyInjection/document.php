<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use setasign\Fpdi\Tfpdf\Fpdi;
use Shopwell\Core\Checkout\Cart\Price\AmountCalculator;
use Shopwell\Core\Checkout\Customer\Service\GuestAuthenticator;
use Shopwell\Core\Checkout\Document\Aggregate\DocumentBaseConfig\DocumentBaseConfigDefinition;
use Shopwell\Core\Checkout\Document\Aggregate\DocumentBaseConfigSalesChannel\DocumentBaseConfigSalesChannelDefinition;
use Shopwell\Core\Checkout\Document\Aggregate\DocumentType\DocumentTypeDefinition;
use Shopwell\Core\Checkout\Document\Aggregate\DocumentTypeTranslation\DocumentTypeTranslationDefinition;
use Shopwell\Core\Checkout\Document\Controller\DocumentController;
use Shopwell\Core\Checkout\Document\DocumentDefinition;
use Shopwell\Core\Checkout\Document\DocumentGeneratorController;
use Shopwell\Core\Checkout\Document\Renderer\CreditNoteRenderer;
use Shopwell\Core\Checkout\Document\Renderer\DeliveryNoteRenderer;
use Shopwell\Core\Checkout\Document\Renderer\DocumentRendererRegistry;
use Shopwell\Core\Checkout\Document\Renderer\InvoiceRenderer;
use Shopwell\Core\Checkout\Document\Renderer\StornoRenderer;
use Shopwell\Core\Checkout\Document\Renderer\ZugferdEmbeddedRenderer;
use Shopwell\Core\Checkout\Document\Renderer\ZugferdRenderer;
use Shopwell\Core\Checkout\Document\SalesChannel\DocumentRoute;
use Shopwell\Core\Checkout\Document\Service\DocumentConfigLoader;
use Shopwell\Core\Checkout\Document\Service\DocumentFileRendererRegistry;
use Shopwell\Core\Checkout\Document\Service\DocumentGenerator;
use Shopwell\Core\Checkout\Document\Service\DocumentMerger;
use Shopwell\Core\Checkout\Document\Service\HtmlRenderer;
use Shopwell\Core\Checkout\Document\Service\PdfRenderer;
use Shopwell\Core\Checkout\Document\Service\ReferenceInvoiceLoader;
use Shopwell\Core\Checkout\Document\Subscriber\DocumentDeleteSubscriber;
use Shopwell\Core\Checkout\Document\Twig\DocumentTemplateRenderer;
use Shopwell\Core\Checkout\Document\Zugferd\ZugferdBuilder;
use Shopwell\Core\Content\Media\MediaService;
use Shopwell\Core\Framework\Adapter\Translation\Translator;
use Shopwell\Core\Framework\Adapter\Twig\TemplateFinder;
use Shopwell\Core\Framework\Extensions\ExtensionDispatcher;
use Shopwell\Core\Framework\Validation\DataValidator;
use Shopwell\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(DocumentDefinition::class)
        ->tag('shopwell.entity.definition')
        ->tag('shopwell.entity.hookable');

    $services->set(DocumentTypeDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(DocumentTypeTranslationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(DocumentBaseConfigDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(DocumentBaseConfigSalesChannelDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(DocumentTemplateRenderer::class)
        ->args([
            service(TemplateFinder::class),
            service('twig'),
            service(Translator::class),
            service(SalesChannelContextFactory::class),
            service('event_dispatcher'),
        ]);

    $services->set(DocumentGeneratorController::class)
        ->public()
        ->args([
            service(DocumentGenerator::class),
            service('serializer'),
            service(DataValidator::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set('pdf.merger', Fpdi::class);

    $services->set(DocumentConfigLoader::class)
        ->args([
            service('document_base_config.repository'),
            service('country.repository'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(ReferenceInvoiceLoader::class)
        ->args([service(Connection::class)]);

    $services->set(InvoiceRenderer::class)
        ->args([
            service('order.repository'),
            service(DocumentConfigLoader::class),
            service('event_dispatcher'),
            service(NumberRangeValueGeneratorInterface::class),
            service(Connection::class),
            service(DocumentFileRendererRegistry::class),
            service('validator'),
        ])
        ->tag('document.renderer');

    $services->set(DeliveryNoteRenderer::class)
        ->args([
            service('order.repository'),
            service(DocumentConfigLoader::class),
            service('event_dispatcher'),
            service(NumberRangeValueGeneratorInterface::class),
            service(Connection::class),
            service(DocumentFileRendererRegistry::class),
        ])
        ->tag('document.renderer');

    $services->set(StornoRenderer::class)
        ->args([
            service('order.repository'),
            service(DocumentConfigLoader::class),
            service('event_dispatcher'),
            service(NumberRangeValueGeneratorInterface::class),
            service(ReferenceInvoiceLoader::class),
            service(Connection::class),
            service(DocumentFileRendererRegistry::class),
            service('validator'),
        ])
        ->tag('document.renderer');

    $services->set(CreditNoteRenderer::class)
        ->args([
            service('order.repository'),
            service(DocumentConfigLoader::class),
            service('event_dispatcher'),
            service(NumberRangeValueGeneratorInterface::class),
            service(ReferenceInvoiceLoader::class),
            service(Connection::class),
            service(DocumentFileRendererRegistry::class),
            service('validator'),
        ])
        ->tag('document.renderer');

    $services->set(DocumentRendererRegistry::class)
        ->args([tagged_iterator('document.renderer')]);

    $services->set(PdfRenderer::class)
        ->args([
            '%shopwell.dompdf.options%',
            service(DocumentTemplateRenderer::class),
            '%kernel.project_dir%',
            service(ExtensionDispatcher::class),
        ])
        ->tag('document_type.renderer', ['key' => 'pdf']);

    $services->set(DocumentGenerator::class)
        ->args([
            service(DocumentRendererRegistry::class),
            service(DocumentFileRendererRegistry::class),
            service(MediaService::class),
            service('document.repository'),
            service(Connection::class),
        ]);

    $services->set(DocumentMerger::class)
        ->args([
            service('document.repository'),
            service(MediaService::class),
            service(DocumentGenerator::class),
            service('pdf.merger'),
            service(Filesystem::class),
        ]);

    $services->set(DocumentController::class)
        ->public()
        ->args([
            service(DocumentGenerator::class),
            service(DocumentMerger::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(DocumentRoute::class)
        ->public()
        ->args([
            service(DocumentGenerator::class),
            service('document.repository'),
            service(GuestAuthenticator::class),
            tagged_iterator('document_type.renderer', indexAttribute: 'key'),
        ]);

    $services->set(HtmlRenderer::class)
        ->args([
            service(DocumentTemplateRenderer::class),
            '%kernel.project_dir%',
            service(ExtensionDispatcher::class),
        ])
        ->tag('document_type.renderer', ['key' => 'html']);

    $services->set(DocumentFileRendererRegistry::class)
        ->args([tagged_iterator('document_type.renderer', indexAttribute: 'key')]);

    $services->set(ZugferdRenderer::class)
        ->args([
            service('order.repository'),
            service(Connection::class),
            service(ZugferdBuilder::class),
            service('event_dispatcher'),
            service(DocumentConfigLoader::class),
            service(NumberRangeValueGeneratorInterface::class),
        ])
        ->tag('document.renderer');

    $services->set(ZugferdEmbeddedRenderer::class)
        ->args([
            service(InvoiceRenderer::class),
            service(ZugferdRenderer::class),
            '%kernel.shopwell_version%',
        ])
        ->tag('document.renderer');

    $services->set(ZugferdBuilder::class)
        ->args([
            service('event_dispatcher'),
            service(AmountCalculator::class),
        ]);

    $services->set(DocumentDeleteSubscriber::class)
        ->args([
            service('document.repository'),
            service('media.repository'),
        ])
        ->tag('kernel.event_subscriber');
};
