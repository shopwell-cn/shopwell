<?php declare(strict_types=1);

namespace Shopwell\Storefront\Page\Product\Configurator;

use Shopwell\Core\Content\Product\SalesChannel\Detail\ProductConfiguratorLoader;
use Shopwell\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopwell\Core\Content\Property\PropertyGroupCollection;
use Shopwell\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;

#[Package('framework')]
class ProductPageConfiguratorLoader extends ProductConfiguratorLoader
{
    /**
     * @internal
     */
    public function __construct(private readonly ProductConfiguratorLoader $loader)
    {
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function load(SalesChannelProductEntity $product, SalesChannelContext $context): PropertyGroupCollection
    {
        return $this->loader->load($product, $context);
    }
}
