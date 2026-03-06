<?php declare(strict_types=1);

namespace Shopwell\Elasticsearch\Product;

use Shopwell\Core\Framework\DataAbstractionLayer\Search\SearchConfigLoader as CoreSearchConfigLoader;
use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
/**
 * @deprecated tag:v6.8.0 - will be removed, use Shopwell\Core\Framework\DataAbstractionLayer\Search\SearchConfigLoader instead
 */
class SearchConfigLoader extends CoreSearchConfigLoader
{
}
