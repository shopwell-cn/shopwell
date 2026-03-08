<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Customer\Aggregate\CustomerGroup;

use Shopwell\Core\Checkout\Customer\Aggregate\CustomerGroupTranslation\CustomerGroupTranslationCollection;
use Shopwell\Core\Checkout\Customer\CustomerCollection;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\SalesChannel\SalesChannelCollection;

#[Package('discovery')]
class CustomerGroupEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    public bool $displayGross;

    protected ?string $name = null;

    protected ?CustomerGroupTranslationCollection $translations = null;

    protected ?CustomerCollection $customers = null;

    protected ?SalesChannelCollection $salesChannels = null;

    protected ?SalesChannelCollection $registrationSalesChannels = null;

    public function getTranslations(): ?CustomerGroupTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(CustomerGroupTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getCustomers(): ?CustomerCollection
    {
        return $this->customers;
    }

    public function setCustomers(CustomerCollection $customers): void
    {
        $this->customers = $customers;
    }

    public function getSalesChannels(): ?SalesChannelCollection
    {
        return $this->salesChannels;
    }

    public function setSalesChannels(SalesChannelCollection $salesChannels): void
    {
        $this->salesChannels = $salesChannels;
    }

    public function getRegistrationSalesChannels(): ?SalesChannelCollection
    {
        return $this->registrationSalesChannels;
    }

    public function setRegistrationSalesChannels(SalesChannelCollection $registrationSalesChannels): void
    {
        $this->registrationSalesChannels = $registrationSalesChannels;
    }
}
