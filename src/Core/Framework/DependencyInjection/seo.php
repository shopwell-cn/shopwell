<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use Shopwell\Core\Content\Seo\Api\SeoActionController;
use Shopwell\Core\Content\Seo\EmptyPathInfoResolver;
use Shopwell\Core\Content\Seo\HreflangLoader;
use Shopwell\Core\Content\Seo\HreflangLoaderInterface;
use Shopwell\Core\Content\Seo\MainCategory\MainCategoryDefinition;
use Shopwell\Core\Content\Seo\MainCategory\SalesChannel\SalesChannelMainCategoryDefinition;
use Shopwell\Core\Content\Seo\SalesChannel\SeoUrlRoute;
use Shopwell\Core\Content\Seo\SalesChannel\StoreApiSeoResolver;
use Shopwell\Core\Content\Seo\SeoResolver;
use Shopwell\Core\Content\Seo\SeoUrl\SalesChannel\SalesChannelSeoUrlDefinition;
use Shopwell\Core\Content\Seo\SeoUrl\SeoUrlDefinition;
use Shopwell\Core\Content\Seo\SeoUrlGenerator;
use Shopwell\Core\Content\Seo\SeoUrlPersister;
use Shopwell\Core\Content\Seo\SeoUrlPlaceholderHandler;
use Shopwell\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopwell\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteRegistry;
use Shopwell\Core\Content\Seo\SeoUrlTemplate\SeoUrlTemplateDefinition;
use Shopwell\Core\Content\Seo\SeoUrlTwigFactory;
use Shopwell\Core\Content\Seo\SeoUrlUpdater;
use Shopwell\Core\Content\Seo\Validation\SeoUrlValidationFactory;
use Shopwell\Core\Framework\Adapter\Twig\Extension\BuildBreadcrumbExtension;
use Shopwell\Core\Framework\Adapter\Twig\Extension\MediaExtension;
use Shopwell\Core\Framework\Adapter\Twig\Extension\RawUrlFunctionExtension;
use Shopwell\Core\Framework\Adapter\Twig\Extension\SeoUrlFunctionExtension;
use Shopwell\Core\Framework\Adapter\Twig\Extension\SwSanitizeTwigFilter;
use Shopwell\Core\Framework\Adapter\Twig\Extension\TwigFeaturesWithInheritanceExtension;
use Shopwell\Core\Framework\Adapter\Twig\TemplateFinder;
use Shopwell\Core\Framework\Adapter\Twig\TwigVariableParserFactory;
use Shopwell\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use Shopwell\Core\Framework\Util\HtmlSanitizer;
use Shopwell\Core\Framework\Validation\DataValidator;
use Shopwell\Core\System\SalesChannel\Entity\SalesChannelDefinitionInstanceRegistry;
use Twig\Environment;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SeoUrlDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SalesChannelSeoUrlDefinition::class)
        ->tag('shopwell.sales_channel.entity.definition');

    $services->set(SeoUrlTemplateDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(MainCategoryDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SalesChannelMainCategoryDefinition::class)
        ->tag('shopwell.sales_channel.entity.definition');

    $services->set(SeoUrlGenerator::class)
        ->args([
            service(DefinitionInstanceRegistry::class),
            service('router.default'),
            service('request_stack'),
            service('shopwell.seo_url.twig'),
            service(TwigVariableParserFactory::class),
            service('logger'),
        ]);

    $services->set(SeoUrlPersister::class)
        ->args([
            service(Connection::class),
            service('seo_url.repository'),
            service('event_dispatcher'),
        ]);

    $services->set(SeoUrlRouteRegistry::class)
        ->lazy()
        ->args([tagged_iterator('shopwell.seo_url.route')]);

    $services->set(EmptyPathInfoResolver::class)
        ->public()
        ->decorate(SeoResolver::class, null, -2000)
        ->args([service('Shopwell\Core\Content\Seo\EmptyPathInfoResolver.inner')]);

    $services->set(SeoResolver::class)
        ->public()
        ->args([service(Connection::class)]);

    $services->set(SeoActionController::class)
        ->public()
        ->args([
            service(SeoUrlGenerator::class),
            service(SeoUrlPersister::class),
            service(DefinitionInstanceRegistry::class),
            service(SeoUrlRouteRegistry::class),
            service(SeoUrlValidationFactory::class),
            service(DataValidator::class),
            service('sales_channel.repository'),
            service(RequestCriteriaBuilder::class),
            service(DefinitionInstanceRegistry::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(SeoUrlValidationFactory::class);

    $services->set(SeoUrlFunctionExtension::class)
        ->args([
            service('twig.extension.routing'),
            service(SeoUrlPlaceholderHandlerInterface::class),
        ])
        ->tag('twig.extension');

    $services->set(TwigFeaturesWithInheritanceExtension::class)
        ->args([service(TemplateFinder::class)])
        ->tag('twig.extension');

    $services->set(SeoUrlPlaceholderHandlerInterface::class, SeoUrlPlaceholderHandler::class)
        ->public()
        ->args([
            service('request_stack'),
            service('router.default'),
            service(Connection::class),
        ]);

    $services->set(MediaExtension::class)
        ->args([service('media.repository')])
        ->tag('twig.extension');

    $services->set(RawUrlFunctionExtension::class)
        ->args([
            service('router'),
            service('request_stack'),
        ])
        ->tag('twig.extension');

    $services->set(SwSanitizeTwigFilter::class)
        ->private()
        ->args([service(HtmlSanitizer::class)])
        ->tag('twig.extension')
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(HreflangLoaderInterface::class, HreflangLoader::class)
        ->args([
            service('router.default'),
            service(Connection::class),
        ]);

    $services->set(SeoUrlRoute::class)
        ->public()
        ->args([service('sales_channel.seo_url.repository')]);

    $services->set(StoreApiSeoResolver::class)
        ->args([
            service('sales_channel.seo_url.repository'),
            service(DefinitionInstanceRegistry::class),
            service(SalesChannelDefinitionInstanceRegistry::class),
            service(SeoUrlRouteRegistry::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(SeoUrlUpdater::class)
        ->args([
            service('language.repository'),
            service(SeoUrlRouteRegistry::class),
            service(SeoUrlGenerator::class),
            service(SeoUrlPersister::class),
            service(Connection::class),
            service('sales_channel.repository'),
        ]);

    $services->set(BuildBreadcrumbExtension::class)
        ->args([
            service(CategoryBreadcrumbBuilder::class),
            service('sales_channel.category.repository'),
        ])
        ->tag('twig.extension');

    $services->set(SeoUrlTwigFactory::class);

    $services->set('shopwell.seo_url.twig', Environment::class)
        ->args([
            service('slugify'),
            tagged_iterator('shopwell.seo_url.twig.extension'),
            '%kernel.cache_dir%',
        ])
        ->factory([service(SeoUrlTwigFactory::class), 'createTwigEnvironment']);
};
