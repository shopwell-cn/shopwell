<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Cart\Order;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;

/**
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class OrderConversionContext extends Struct
{
    protected bool $includeCustomer = true;

    protected bool $includeBillingAddress = true;

    protected bool $includeDeliveries = true;

    protected bool $includeTransactions = true;

    protected bool $includePersistentData = true;

    protected bool $includeOrderNumber = true;

    public function shouldIncludeCustomer(): bool
    {
        return $this->includeCustomer;
    }

    public function setIncludeCustomer(bool $includeCustomer): OrderConversionContext
    {
        $this->includeCustomer = $includeCustomer;

        return $this;
    }

    public function shouldIncludeBillingAddress(): bool
    {
        return $this->includeBillingAddress;
    }

    public function setIncludeBillingAddress(bool $includeBillingAddress): OrderConversionContext
    {
        $this->includeBillingAddress = $includeBillingAddress;

        return $this;
    }

    public function shouldIncludeDeliveries(): bool
    {
        return $this->includeDeliveries;
    }

    public function setIncludeDeliveries(bool $includeDeliveries): OrderConversionContext
    {
        $this->includeDeliveries = $includeDeliveries;

        return $this;
    }

    public function shouldIncludeTransactions(): bool
    {
        return $this->includeTransactions;
    }

    public function setIncludeTransactions(bool $includeTransactions): OrderConversionContext
    {
        $this->includeTransactions = $includeTransactions;

        return $this;
    }

    public function shouldIncludePersistentData(): bool
    {
        return $this->includePersistentData;
    }

    public function setIncludePersistentData(bool $includePersistentData): OrderConversionContext
    {
        $this->includePersistentData = $includePersistentData;

        return $this;
    }

    public function shouldIncludeOrderNumber(): bool
    {
        return $this->includeOrderNumber;
    }

    public function setIncludeOrderNumber(bool $includeOrderNumber): OrderConversionContext
    {
        $this->includeOrderNumber = $includeOrderNumber;

        return $this;
    }

    public function getApiAlias(): string
    {
        return 'cart_order_conversion_context';
    }
}
