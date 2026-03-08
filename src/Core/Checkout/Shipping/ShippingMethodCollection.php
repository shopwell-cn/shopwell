<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Shipping;

use Shopwell\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceCollection;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;

/**
 * @extends EntityCollection<ShippingMethodEntity>
 */
#[Package('checkout')]
class ShippingMethodCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getPriceIds(): array
    {
        $ids = [[]];

        foreach ($this->getIterator() as $element) {
            $ids[] = $element->getPrices()->getIds();
        }

        return array_merge(...$ids);
    }

    public function getPrices(): ShippingMethodPriceCollection
    {
        $prices = [[]];

        foreach ($this->getIterator() as $element) {
            $prices[] = $element->getPrices()->getElements();
        }

        $prices = array_merge(...$prices);

        return new ShippingMethodPriceCollection($prices);
    }

    /**
     * Sorts the selected shipping method first
     * If a different default shipping method is defined, it will be sorted second
     * All other shipping methods keep their respective sorting
     */
    public function sortShippingMethodsByPreference(SalesChannelContext $context): void
    {
        $ids = array_merge(
            [$context->getSalesChannel()->getShippingMethodId()],
            $this->getIds(),
        );

        $this->sortByIdArray($ids);
    }

    public function getApiAlias(): string
    {
        return 'shipping_method_collection';
    }

    protected function getExpectedClass(): string
    {
        return ShippingMethodEntity::class;
    }
}
