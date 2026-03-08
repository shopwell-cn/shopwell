<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Category\SalesChannel;

use Shopwell\Core\Content\Category\Aggregate\CategoryTranslation\CategoryTranslationEntity;
use Shopwell\Core\Content\Category\CategoryCollection;
use Shopwell\Core\Content\Category\CategoryDefinition;
use Shopwell\Core\Content\Category\CategoryEntity;
use Shopwell\Core\Content\Category\CategoryException;
use Shopwell\Core\Framework\Adapter\Cache\CacheTagCollector;
use Shopwell\Core\Framework\Adapter\Request\RequestParamHelper;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopwell\Core\Framework\Routing\StoreApiRouteScope;
use Shopwell\Core\PlatformRequest;
use Shopwell\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('discovery')]
class CategoryRoute extends AbstractCategoryRoute
{
    final public const string HOME = 'home';

    /**
     * @internal
     *
     * @param SalesChannelRepository<CategoryCollection> $categoryRepository
     */
    public function __construct(
        private readonly SalesChannelRepository $categoryRepository,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public static function buildName(string $id): string
    {
        return 'category-route-' . $id;
    }

    public function getDecorated(): AbstractCategoryRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/category/{navigationId}',
        name: 'store-api.category.detail',
        methods: [Request::METHOD_GET, Request::METHOD_POST],
        defaults: [PlatformRequest::ATTRIBUTE_HTTP_CACHE => true],
    )]
    public function load(string $navigationId, Request $request, SalesChannelContext $context): CategoryRouteResponse
    {
        $this->cacheTagCollector->addTag(self::buildName($navigationId));

        if ($navigationId === self::HOME) {
            $navigationId = $context->getSalesChannel()->getNavigationCategoryId();
            $request->attributes->set('navigationId', $navigationId);

            $routeParams = $request->attributes->get('_route_params', []);
            $routeParams['navigationId'] = $navigationId;
            $request->attributes->set('_route_params', $routeParams);
        }

        $category = $this->loadCategory($navigationId, $context);

        $categoryHasContentlessPageType = \in_array($category->getType(), [CategoryDefinition::TYPE_FOLDER, CategoryDefinition::TYPE_LINK], true);
        if ($categoryHasContentlessPageType && $context->getSalesChannel()->getNavigationCategoryId() !== $navigationId) {
            if ($category->getType() === CategoryDefinition::TYPE_LINK) {
                return new CategoryRouteResponse($category);
            }

            throw CategoryException::categoryNotFound($navigationId);
        }

        return new CategoryRouteResponse($category);
    }

    private function loadCategory(string $categoryId, SalesChannelContext $context): CategoryEntity
    {
        $criteria = new Criteria([$categoryId]);
        $criteria->setTitle('category::data');

        $criteria->addAssociation('media');
        $criteria->addAssociation('translations');

        $category = $this->categoryRepository->search($criteria, $context)->getEntities()->get($categoryId);
        if (!$category instanceof CategoryEntity) {
            throw CategoryException::categoryNotFound($categoryId);
        }

        return $category;
    }

    private function createCriteria(string $pageId, Request $request): Criteria
    {
        $criteria = new Criteria([$pageId]);
        $criteria->setTitle('category::cms-page');

        $slots = RequestParamHelper::get($request, 'slots');

        if (\is_string($slots)) {
            $slots = explode('|', $slots);
        }

        if (!empty($slots) && \is_array($slots)) {
            $criteria
                ->getAssociation('sections.blocks')
                ->addFilter(new EqualsAnyFilter('slots.id', $slots));
        }

        return $criteria;
    }

    /**
     * @return array<string, array<string, mixed>>|null
     */
    private function buildMergedCmsSlotConfig(CategoryEntity $category, SalesChannelContext $context): ?array
    {
        $inheritanceChain = $context->getLanguageIdChain();
        if (\count($inheritanceChain) <= 1) {
            return $category->getTranslation('slotConfig');
        }

        /** @var non-empty-list<string> $languageMergeOrder */
        $languageMergeOrder = \array_reverse(\array_unique($inheritanceChain));
        $translatedSlotConfigs = $this->getTranslatedSlotConfigs($category, $languageMergeOrder);

        return \array_merge(...$translatedSlotConfigs);
    }

    /**
     * @param non-empty-list<string> $languageMergeOrder
     *
     * @return non-empty-list<array<string, array<string, mixed>>>
     */
    private function getTranslatedSlotConfigs(CategoryEntity $category, array $languageMergeOrder): array
    {
        $getCategoryTranslationByLanguageId = static function (CategoryEntity $category, string $languageId): ?CategoryTranslationEntity {
            return \array_find(
                $category->getTranslations()?->getElements() ?? [],
                static fn (CategoryTranslationEntity $translation) => $translation->getLanguageId() === $languageId,
            );
        };

        return \array_map(static function (string $languageId) use ($category, $getCategoryTranslationByLanguageId) {
            $currentTranslation = $getCategoryTranslationByLanguageId($category, $languageId);

            return $currentTranslation?->getSlotConfig() ?? [];
        }, $languageMergeOrder);
    }
}
