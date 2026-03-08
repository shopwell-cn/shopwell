<?php declare(strict_types=1);

namespace Shopwell\Core\System\Country\Aggregate\CountryState;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<CountryStateEntity>
 */
#[Package('fundamentals@discovery')]
class CountryStateCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getCountryIds(): array
    {
        return $this->fmap(fn (CountryStateEntity $countryState) => $countryState->getCountryId());
    }

    public function filterByCountryId(string $id): self
    {
        return $this->filter(fn (CountryStateEntity $countryState) => $countryState->getCountryId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'country_state_collection';
    }

    protected function getExpectedClass(): string
    {
        return CountryStateEntity::class;
    }
}
