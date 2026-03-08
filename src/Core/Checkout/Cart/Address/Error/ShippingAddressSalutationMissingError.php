<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Cart\Address\Error;

use Shopwell\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopwell\Core\Framework\Log\Package;

#[Package('checkout')]
class ShippingAddressSalutationMissingError extends SalutationMissingError
{
    protected const string KEY = parent::KEY . '-shipping-address';

    public function __construct(
        private readonly CustomerAddressEntity $address
    ) {
        $this->message = \sprintf(
            'A salutation needs to be defined for the shipping address "%s %s, %s %s".',
            $address->getFirstName(),
            $address->getLastName(),
            (string) $address->getZipcode(),
            $address->getCity()
        );

        $this->parameters = [
            'addressId' => $address->getId(),
        ];

        parent::__construct($this->message);
    }

    public function getId(): string
    {
        return self::KEY;
    }

    public function getAddressId(): ?string
    {
        return $this->address->getId();
    }
}
