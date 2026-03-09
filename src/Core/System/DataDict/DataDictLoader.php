<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;

#[Package('data-services')]
class DataDictLoader extends AbstractDataDictLoader
{
    /**
     * @internal
     * @param EntityRepository<DataDictGroupCollection> $dictGroupRepository
     */
    public function __construct(
        private readonly EntityRepository $dictGroupRepository,
    ) {
    }

    public function getDecorated(): AbstractDataDictLoader
    {
        throw new DecorationPatternException(self::class);
    }

    public function load(): array
    {
        $criteria = new Criteria()
            ->addAssociation('translations')
            ->addAssociation('items')
            ->addFilter(
                new EqualsFilter('active', true),
                new EqualsFilter('items.active', true)
            );

        $this->dictGroupRepository->search($criteria, Context::createDefaultContext())->getElements();

        return [];
    }
}
