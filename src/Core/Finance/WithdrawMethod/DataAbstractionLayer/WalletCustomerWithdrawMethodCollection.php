<?php declare(strict_types=1);

namespace Shopwell\Core\Finance\WithdrawMethod\DataAbstractionLayer;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<WalletCustomerWithdrawMethodEntity>
 */
#[Package('fundamentals@checkout')]
class WalletCustomerWithdrawMethodCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'wallet_customer_withdraw_collection';
    }

    protected function getExpectedClass(): string
    {
        return WalletCustomerWithdrawMethodEntity::class;
    }
}
