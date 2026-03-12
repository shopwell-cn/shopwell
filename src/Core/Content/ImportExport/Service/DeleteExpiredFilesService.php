<?php declare(strict_types=1);

namespace Shopwell\Core\Content\ImportExport\Service;

use Shopwell\Core\Content\ImportExport\Aggregate\ImportExportFile\ImportExportFileEntity;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopwell\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
class DeleteExpiredFilesService
{
    /**
     * @param EntityRepository<EntityCollection<ImportExportFileEntity>> $fileRepository
     */
    public function __construct(private readonly EntityRepository $fileRepository)
    {
    }

    public function countFiles(Context $context): int
    {
        $criteria = $this->buildCriteria();
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);

        return $this->fileRepository->searchIds($criteria, $context)->getTotal();
    }

    public function deleteFiles(Context $context): void
    {
        $criteria = $this->buildCriteria();

        $ids = $this->fileRepository->searchIds($criteria, $context)->getIds();
        $ids = array_map(static fn ($id) => ['id' => $id], $ids);
        $this->fileRepository->delete($ids, $context);
    }

    private function buildCriteria(): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new RangeFilter(
            'expireDate',
            [
                RangeFilter::LT => new \DateTimeImmutable('-30 days')->format(\DATE_ATOM),
            ]
        ));

        return $criteria;
    }
}
