<?php declare(strict_types=1);

namespace Shopwell\Core\System\Country;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<CountryEntity>
 */
#[Package('fundamentals@discovery')]
class CountryCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'country_collection';
    }

    protected function getExpectedClass(): string
    {
        return CountryEntity::class;
    }
}
