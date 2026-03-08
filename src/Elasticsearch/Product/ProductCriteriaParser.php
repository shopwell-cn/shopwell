<?php declare(strict_types=1);

namespace Shopwell\Elasticsearch\Product;

use OpenSearchDSL\BuilderInterface;
use OpenSearchDSL\Query\Compound\BoolQuery;
use OpenSearchDSL\Query\TermLevel\ExistsQuery;
use OpenSearchDSL\Query\TermLevel\RangeQuery;
use OpenSearchDSL\Query\TermLevel\TermQuery;
use Shopwell\Core\Content\Product\ProductDefinition;
use Shopwell\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\CustomField\CustomFieldService;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\CriteriaParser;

/**
 * @internal - This class is part of the internal API, optimized for read and should not be used directly.
 */
#[Package('inventory')]
class ProductCriteriaParser extends CriteriaParser
{
    public function __construct(
        EntityDefinitionQueryHelper $helper,
        CustomFieldService $customFieldService,
        private readonly CriteriaParser $decorated
    ) {
        parent::__construct($helper, $customFieldService);
    }

    public function parseFilter(Filter $filter, EntityDefinition $definition, string $root, Context $context): BuilderInterface
    {
        if (!$definition instanceof ProductDefinition) {
            return parent::parseFilter($filter, $definition, $root, $context);
        }

        if ($filter instanceof ProductAvailableFilter) {
            $query = new BoolQuery();

            $query->add(
                new TermQuery('active', true),
            );

            $query->add(
                new RangeQuery('visibility_' . $filter->getSalesChannelId(), [RangeFilter::GTE => $filter->getVisibility()]),
            );

            return $query;
        }

        if ($filter instanceof EqualsFilter && \str_contains($filter->getField(), 'categoriesRo.id')) {
            if ($filter->getValue() === null) {
                return new BoolQuery([
                    BoolQuery::MUST_NOT => new ExistsQuery('categoryTree'),
                ]);
            }

            return new TermQuery('categoryTree', $filter->getValue());
        }

        return parent::parseFilter($filter, $definition, $root, $context);
    }
}
