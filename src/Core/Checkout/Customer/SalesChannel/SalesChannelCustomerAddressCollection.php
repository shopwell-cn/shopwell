<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Customer\SalesChannel;

use Shopwell\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressCollection;
use Shopwell\Core\Framework\Log\Package;

#[Package('checkout')]
class SalesChannelCustomerAddressCollection extends CustomerAddressCollection
{
    protected function getExpectedClass(): string
    {
        return SalesChannelCustomerAddressEntity::class;
    }
}
