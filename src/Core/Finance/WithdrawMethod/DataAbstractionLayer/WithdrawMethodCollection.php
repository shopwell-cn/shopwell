<?php declare(strict_types=1);

namespace Shopwell\Core\Finance\WithdrawMethod\DataAbstractionLayer;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<WithdrawMethodEntity>
 */
#[Package('fundamentals@checkout')]
class WithdrawMethodCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'wallet_withdraw_method_collection';
    }

    protected function getExpectedClass(): string
    {
        return WithdrawMethodEntity::class;
    }
}
