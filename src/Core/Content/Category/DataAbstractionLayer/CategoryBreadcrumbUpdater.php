<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Category\DataAbstractionLayer;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopwell\Core\Content\Category\CategoryCollection;
use Shopwell\Core\Content\Category\CategoryException;
use Shopwell\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopwell\Core\Defaults;
use Shopwell\Core\Framework\Api\Context\SystemSource;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Uuid\Uuid;
use Shopwell\Core\System\Language\LanguageCollection;

#[Package('discovery')]
class CategoryBreadcrumbUpdater
{
    /**
     * @internal
     *
     * @param EntityRepository<CategoryCollection> $categoryRepository
     * @param EntityRepository<LanguageCollection> $languageRepository
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityRepository $categoryRepository,
        private readonly EntityRepository $languageRepository
    ) {
    }

    /**
     * @param string[] $ids
     */
    public function update(array $ids, Context $context): void
    {
        if ($ids === []) {
            return;
        }

        $versionId = Uuid::fromHexToBytes($context->getVersionId());

        $query = $this->connection->createQueryBuilder();
        $query->select('category.path');
        $query->from('category');
        $query->where('category.id IN (:ids)');
        $query->andWhere('category.version_id = :version');
        $query->setParameter('version', $versionId);
        $query->setParameter('ids', Uuid::fromHexToBytesList($ids), ArrayParameterType::BINARY);

        $paths = $query->executeQuery()->fetchFirstColumn();

        $all = $ids;
        foreach ($paths as $path) {
            $path = explode('|', (string) $path);
            foreach ($path as $id) {
                $all[] = $id;
            }
        }

        $all = array_filter(array_keys(array_flip($all)));
        $languageCriteria = new Criteria();
        $languageCriteria->addFilter(new EqualsFilter('active', true));

        $languages = $this->languageRepository->search($languageCriteria, $context)->getEntities();

        foreach ($languages as $language) {
            $context = new Context(
                new SystemSource(),
                [],
                Defaults::CURRENCY,
                array_values(array_filter([$language->getId(), $language->getParentId(), Defaults::LANGUAGE_SYSTEM])),
                Defaults::LIVE_VERSION
            );

            $this->updateLanguage($ids, $context, $all);
        }
    }

    /**
     * @param string[] $ids
     * @param string[] $all
     */
    private function updateLanguage(array $ids, Context $context, array $all): void
    {
        $versionId = Uuid::fromHexToBytes($context->getVersionId());
        $languageId = Uuid::fromHexToBytes($context->getLanguageId());

        $categories = $this->categoryRepository
            ->search(new Criteria($all), $context)
            ->getEntities();

        $update = $this->connection->prepare('
            INSERT INTO `category_translation` (`category_id`, `category_version_id`, `language_id`, `breadcrumb`, `created_at`)
            VALUES (:categoryId, :versionId, :languageId, :breadcrumb, DATE(NOW()))
            ON DUPLICATE KEY UPDATE `breadcrumb` = :breadcrumb
        ');
        $update = new RetryableQuery($this->connection, $update);

        foreach ($ids as $id) {
            try {
                $path = $this->buildBreadcrumb($id, $categories);
            } catch (CategoryNotFoundException) {
                continue;
            }

            $update->execute([
                'categoryId' => Uuid::fromHexToBytes($id),
                'versionId' => $versionId,
                'languageId' => $languageId,
                'breadcrumb' => json_encode($path, \JSON_THROW_ON_ERROR),
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function buildBreadcrumb(string $id, CategoryCollection $categories): array
    {
        $category = $categories->get($id);

        if (!$category) {
            throw CategoryException::categoryNotFound($id);
        }

        $breadcrumb = [];
        if ($category->getParentId()) {
            $breadcrumb = $this->buildBreadcrumb($category->getParentId(), $categories);
        }

        $breadcrumb[$category->getId()] = $category->getTranslation('name');

        return $breadcrumb;
    }
}
