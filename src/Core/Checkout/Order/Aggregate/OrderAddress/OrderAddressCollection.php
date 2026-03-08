<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Order\Aggregate\OrderAddress;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\Country\Aggregate\CountryState\CountryStateCollection;
use Shopwell\Core\System\Country\CountryCollection;

/**
 * @extends EntityCollection<OrderAddressEntity>
 */
#[Package('checkout')]
class OrderAddressCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getCountryIds(): array
    {
        return $this->fmap(fn (OrderAddressEntity $orderAddress) => $orderAddress->getCountryId());
    }

    public function filterByCountryId(string $id): self
    {
        return $this->filter(fn (OrderAddressEntity $orderAddress) => $orderAddress->getCountryId() === $id);
    }

    /**
     * @return array<string>
     */
    public function getCountryStateIds(): array
    {
        return $this->fmap(fn (OrderAddressEntity $orderAddress) => $orderAddress->getCountryStateId());
    }

    public function filterByCountryStateId(string $id): self
    {
        return $this->filter(fn (OrderAddressEntity $orderAddress) => $orderAddress->getCountryStateId() === $id);
    }

    public function getCountries(): CountryCollection
    {
        return new CountryCollection(
            $this->fmap(fn (OrderAddressEntity $orderAddress) => $orderAddress->getCountry())
        );
    }

    public function getCountryStates(): CountryStateCollection
    {
        return new CountryStateCollection(
            $this->fmap(fn (OrderAddressEntity $orderAddress) => $orderAddress->getCountryState())
        );
    }

    public function getApiAlias(): string
    {
        return 'order_address_collection';
    }

    protected function getExpectedClass(): string
    {
        return OrderAddressEntity::class;
    }
}
