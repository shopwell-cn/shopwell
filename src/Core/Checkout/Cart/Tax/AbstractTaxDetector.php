<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Cart\Tax;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
abstract class AbstractTaxDetector
{
    abstract public function getDecorated(): AbstractTaxDetector;

    abstract public function useGross(SalesChannelContext $context): bool;

    abstract public function isNetDelivery(SalesChannelContext $context): bool;

    abstract public function getTaxState(SalesChannelContext $context): string;
}
