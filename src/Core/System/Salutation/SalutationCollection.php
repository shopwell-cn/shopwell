<?php declare(strict_types=1);

namespace Shopwell\Core\System\Salutation;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<SalutationEntity>
 */
#[Package('checkout')]
class SalutationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'salutation_collection';
    }

    protected function getExpectedClass(): string
    {
        return SalutationEntity::class;
    }
}
