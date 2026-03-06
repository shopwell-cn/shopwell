<?php declare(strict_types=1);

namespace Shopwell\Core\Content\ProductStream\Service;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use Shopwell\Core\Framework\Log\Package;

#[Package('inventory')]
interface ProductStreamBuilderInterface
{
    /**
     * @return array<int, Filter>
     */
    public function buildFilters(
        string $id,
        Context $context
    ): array;
}
