<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Wallet\Aggregate\WalletWithdrawMethod;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<WalletWithdrawMethodEntity>
 */
#[Package('checkout')]
class WalletWithdrawMethodCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'wallet_withdraw_method_collection';
    }

    protected function getExpectedClass(): string
    {
        return WalletWithdrawMethodEntity::class;
    }
}
