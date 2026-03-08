<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Cart\Tax;

use Shopwell\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class TaxDetector extends AbstractTaxDetector
{
    public function getDecorated(): AbstractTaxDetector
    {
        throw new DecorationPatternException(self::class);
    }

    public function useGross(SalesChannelContext $context): bool
    {
        return $context->getCurrentCustomerGroup()->displayGross;
    }

    public function isNetDelivery(SalesChannelContext $context): bool
    {
        $shippingLocationCountry = $context->getShippingLocation()->getCountry();
        $countryTaxFree = $shippingLocationCountry->getCustomerTax()->getEnabled();

        if ($countryTaxFree) {
            return true;
        }

        return false;
    }

    public function getTaxState(SalesChannelContext $context): string
    {
        if ($this->isNetDelivery($context)) {
            return CartPrice::TAX_STATE_FREE;
        }

        if ($this->useGross($context)) {
            return CartPrice::TAX_STATE_GROSS;
        }

        return CartPrice::TAX_STATE_NET;
    }
}
