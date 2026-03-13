<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Cart\Tax;

use Shopwell\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopwell\Core\System\Country\CountryEntity;
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
        $countryTaxFree = $shippingLocationCountry->getCustomerTax()->enabled;

        if ($countryTaxFree) {
            return true;
        }

        return $this->isCompanyTaxFree($context, $shippingLocationCountry);
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

    public function isCompanyTaxFree(SalesChannelContext $context, CountryEntity $shippingLocationCountry): bool
    {
        $customer = $context->getCustomer();

        $countryCompanyTaxFree = $shippingLocationCountry->companyTax->enabled;

        if (!$countryCompanyTaxFree || !$customer || !$customer->getCompany()) {
            return false;
        }

        if (!$shippingLocationCountry->isEu) {
            return true;
        }

        $vatPattern = $shippingLocationCountry->vatIdPattern;
        $vatIds = array_filter($customer->getVatIds() ?? []);

        if ($vatIds === []) {
            return false;
        }

        if ($vatPattern !== null && $vatPattern !== '' && $shippingLocationCountry->checkVatIdPattern) {
            $regex = '/^' . $vatPattern . '$/';

            if (array_any($vatIds, fn ($vatId) => !preg_match($regex, $vatId))) {
                return false;
            }
        }

        return true;
    }
}
