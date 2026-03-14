<?php declare(strict_types=1);

namespace Shopwell\Core\System\Country;

use Shopwell\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressCollection;
use Shopwell\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressCollection;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopwell\Core\Framework\DataAbstractionLayer\TaxFreeConfig;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\Country\Aggregate\CountryState\CountryStateCollection;
use Shopwell\Core\System\Country\Aggregate\CountryTranslation\CountryTranslationCollection;
use Shopwell\Core\System\Currency\Aggregate\CurrencyCountryRounding\CurrencyCountryRoundingCollection;
use Shopwell\Core\System\SalesChannel\SalesChannelCollection;
use Shopwell\Core\System\Tax\Aggregate\TaxRule\TaxRuleCollection;

#[Package('fundamentals@discovery')]
class CountryEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    public ?string $iso3 = null;

    public bool $isEu;

    public bool $checkVatIdPattern;

    public bool $vatIdRequired;

    public bool $forceStateInRegistration;

    public ?string $vatIdPattern = null;

    public TaxFreeConfig $companyTax;

    public bool $displayStateInRegistration;

    public TaxFreeConfig $customerTax;

    protected ?string $name = null;

    protected ?string $iso = null;

    protected int $position;

    protected bool $active;

    protected bool $shippingAvailable;

    protected ?CountryStateCollection $states = null;

    protected ?CountryTranslationCollection $translations = null;

    protected ?OrderAddressCollection $orderAddresses = null;

    protected ?CustomerAddressCollection $customerAddresses = null;

    protected ?SalesChannelCollection $salesChannelDefaultAssignments = null;

    protected ?SalesChannelCollection $salesChannels = null;

    protected ?TaxRuleCollection $taxRules = null;

    protected ?CurrencyCountryRoundingCollection $currencyCountryRoundings = null;

    protected bool $postalCodeRequired;

    protected bool $checkPostalCodePattern;

    protected bool $checkAdvancedPostalCodePattern;

    protected ?string $advancedPostalCodePattern = null;

    protected ?string $defaultPostalCodePattern = null;

    /**
     * @var list<list<string>>
     */
    protected array $addressFormat;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getIso(): ?string
    {
        return $this->iso;
    }

    public function setIso(?string $iso): void
    {
        $this->iso = $iso;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getShippingAvailable(): bool
    {
        return $this->shippingAvailable;
    }

    public function setShippingAvailable(bool $shippingAvailable): void
    {
        $this->shippingAvailable = $shippingAvailable;
    }

    public function getStates(): ?CountryStateCollection
    {
        return $this->states;
    }

    public function setStates(CountryStateCollection $states): void
    {
        $this->states = $states;
    }

    public function getTranslations(): ?CountryTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(CountryTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getOrderAddresses(): ?OrderAddressCollection
    {
        return $this->orderAddresses;
    }

    public function setOrderAddresses(OrderAddressCollection $orderAddresses): void
    {
        $this->orderAddresses = $orderAddresses;
    }

    public function getCustomerAddresses(): ?CustomerAddressCollection
    {
        return $this->customerAddresses;
    }

    public function setCustomerAddresses(CustomerAddressCollection $customerAddresses): void
    {
        $this->customerAddresses = $customerAddresses;
    }

    public function getSalesChannelDefaultAssignments(): ?SalesChannelCollection
    {
        return $this->salesChannelDefaultAssignments;
    }

    public function setSalesChannelDefaultAssignments(SalesChannelCollection $salesChannelDefaultAssignments): void
    {
        $this->salesChannelDefaultAssignments = $salesChannelDefaultAssignments;
    }

    public function getSalesChannels(): ?SalesChannelCollection
    {
        return $this->salesChannels;
    }

    public function setSalesChannels(SalesChannelCollection $salesChannels): void
    {
        $this->salesChannels = $salesChannels;
    }

    public function getTaxRules(): ?TaxRuleCollection
    {
        return $this->taxRules;
    }

    public function setTaxRules(TaxRuleCollection $taxRules): void
    {
        $this->taxRules = $taxRules;
    }

    public function getCurrencyCountryRoundings(): ?CurrencyCountryRoundingCollection
    {
        return $this->currencyCountryRoundings;
    }

    public function setCurrencyCountryRoundings(CurrencyCountryRoundingCollection $currencyCountryRoundings): void
    {
        $this->currencyCountryRoundings = $currencyCountryRoundings;
    }

    public function getCustomerTax(): TaxFreeConfig
    {
        return $this->customerTax;
    }

    public function setCustomerTax(TaxFreeConfig $customerTax): void
    {
        $this->customerTax = $customerTax;
    }

    public function getPostalCodeRequired(): bool
    {
        return $this->postalCodeRequired;
    }

    public function setPostalCodeRequired(bool $postalCodeRequired): void
    {
        $this->postalCodeRequired = $postalCodeRequired;
    }

    public function getCheckPostalCodePattern(): bool
    {
        return $this->checkPostalCodePattern;
    }

    public function setCheckPostalCodePattern(bool $checkPostalCodePattern): void
    {
        $this->checkPostalCodePattern = $checkPostalCodePattern;
    }

    public function getCheckAdvancedPostalCodePattern(): bool
    {
        return $this->checkAdvancedPostalCodePattern;
    }

    public function setCheckAdvancedPostalCodePattern(bool $checkAdvancedPostalCodePattern): void
    {
        $this->checkAdvancedPostalCodePattern = $checkAdvancedPostalCodePattern;
    }

    public function getAdvancedPostalCodePattern(): ?string
    {
        return $this->advancedPostalCodePattern;
    }

    public function setAdvancedPostalCodePattern(?string $advancedPostalCodePattern): void
    {
        $this->advancedPostalCodePattern = $advancedPostalCodePattern;
    }

    /**
     * @return list<list<string>>
     */
    public function getAddressFormat(): array
    {
        return $this->addressFormat;
    }

    /**
     * @param list<list<string>> $addressFormat
     */
    public function setAddressFormat(array $addressFormat): void
    {
        $this->addressFormat = $addressFormat;
    }

    public function setDefaultPostalCodePattern(?string $pattern): void
    {
        $this->defaultPostalCodePattern = $pattern;
    }

    public function getDefaultPostalCodePattern(): ?string
    {
        return $this->defaultPostalCodePattern;
    }
}
