<?php declare(strict_types=1);

namespace Shopwell\Core\Finance\Wallet\Aggregate\WalletFreeze;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<WalletFreezeEntity>
 */
#[Package('fundamentals@framework')]
class WalletFreezeCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'wallet_freeze_collection';
    }

    protected function getExpectedClass(): string
    {
        return WalletFreezeEntity::class;
    }
}
