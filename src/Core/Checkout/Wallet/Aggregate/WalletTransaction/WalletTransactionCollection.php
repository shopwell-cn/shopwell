<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Wallet\Aggregate\WalletTransaction;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<WalletTransactionEntity>
 */
#[Package('checkout')]
class WalletTransactionCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'wallet_transaction_collection';
    }

    protected function getExpectedClass(): string
    {
        return WalletTransactionEntity::class;
    }
}
