<?php declare(strict_types=1);

namespace Shopwell\Core\Content\ProductStream\Service;

use Shopwell\Core\Content\ProductStream\Exception\NoFilterException;
use Shopwell\Core\Content\ProductStream\ProductStreamCollection;
use Shopwell\Core\Content\ProductStream\ProductStreamEntity;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Exception\EntityNotFoundException;
use Shopwell\Core\Framework\DataAbstractionLayer\Exception\SearchRequestException;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Parser\QueryStringParser;
use Shopwell\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductStreamBuilder implements ProductStreamBuilderInterface
{
    /**
     * @internal
     *
     * @param EntityRepository<ProductStreamCollection> $repository
     */
    public function __construct(
        private readonly EntityRepository $repository,
        private readonly EntityDefinition $productDefinition
    ) {
    }

    public function buildFilters(string $id, Context $context): array
    {
        $criteria = new Criteria([$id]);

        /** @var ProductStreamEntity|null $stream */
        $stream = $this->repository
            ->search($criteria, $context)
            ->get($id);

        if (!$stream) {
            throw new EntityNotFoundException('product_stream', $id);
        }

        $data = $stream->getApiFilter();
        if (!$data) {
            throw new NoFilterException($id);
        }

        $filters = [];
        $exception = new SearchRequestException();

        foreach ($data as $filter) {
            $filters[] = QueryStringParser::fromArray($this->productDefinition, $filter, $exception, '');
        }

        return $filters;
    }
}
