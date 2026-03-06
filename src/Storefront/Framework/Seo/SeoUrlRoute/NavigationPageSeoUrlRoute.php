<?php declare(strict_types=1);

namespace Shopwell\Storefront\Framework\Seo\SeoUrlRoute;

use Shopwell\Core\Content\Category\CategoryDefinition;
use Shopwell\Core\Content\Category\CategoryEntity;
use Shopwell\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use Shopwell\Core\Content\Seo\SeoUrlRoute\SeoUrlMapping;
use Shopwell\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteConfig;
use Shopwell\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteInterface;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\SalesChannel\SalesChannelEntity;

#[Package('inventory')]
class NavigationPageSeoUrlRoute implements SeoUrlRouteInterface
{
    final public const ROUTE_NAME = 'frontend.navigation.page';
    final public const DEFAULT_TEMPLATE = '{% for part in category.seoBreadcrumb %}{{ part }}/{% endfor %}';

    /**
     * @internal
     */
    public function __construct(
        private readonly CategoryDefinition $categoryDefinition,
        private readonly CategoryBreadcrumbBuilder $breadcrumbBuilder
    ) {
    }

    public function getConfig(): SeoUrlRouteConfig
    {
        return new SeoUrlRouteConfig(
            $this->categoryDefinition,
            self::ROUTE_NAME,
            self::DEFAULT_TEMPLATE,
            true
        );
    }

    public function prepareCriteria(Criteria $criteria, SalesChannelEntity $salesChannel): void
    {
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
            new EqualsFilter('active', true),
            new NotFilter(NotFilter::CONNECTION_OR, [
                new EqualsFilter('type', CategoryDefinition::TYPE_FOLDER),
                new EqualsFilter('type', CategoryDefinition::TYPE_LINK),
            ]),
        ]));
    }

    public function getMapping(Entity $category, ?SalesChannelEntity $salesChannel): SeoUrlMapping
    {
        if (!$category instanceof CategoryEntity) {
            throw new \InvalidArgumentException('Expected CategoryEntity');
        }

        $rootId = $this->detectRootId($category, $salesChannel);

        $breadcrumbs = $this->breadcrumbBuilder->build($category, $salesChannel, $rootId);
        $categoryJson = $category->jsonSerialize();
        $categoryJson['seoBreadcrumb'] = $breadcrumbs;

        $error = null;
        if (!$rootId) {
            $error = 'Category is not available for sales channel';
        }

        return new SeoUrlMapping(
            $category,
            ['navigationId' => $category->getId()],
            [
                'category' => $categoryJson,
            ],
            $error
        );
    }

    private function detectRootId(CategoryEntity $category, ?SalesChannelEntity $salesChannel): ?string
    {
        if (!$salesChannel) {
            return null;
        }
        $path = array_filter(explode('|', (string) $category->getPath()));

        $navigationId = $salesChannel->getNavigationCategoryId();
        if ($navigationId === $category->getId() || \in_array($navigationId, $path, true)) {
            return $navigationId;
        }

        $footerId = $salesChannel->getFooterCategoryId();
        if ($footerId === $category->getId() || \in_array($footerId, $path, true)) {
            return $footerId;
        }

        $serviceId = $salesChannel->getServiceCategoryId();
        if ($serviceId === $category->getId() || \in_array($serviceId, $path, true)) {
            return $serviceId;
        }

        return null;
    }
}
