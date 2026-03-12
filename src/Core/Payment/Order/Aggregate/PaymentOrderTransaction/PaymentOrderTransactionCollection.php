<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Order\Aggregate\PaymentOrderTransaction;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PaymentOrderTransactionEntity>
 */
#[Package('payment-system')]
class PaymentOrderTransactionCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'payment_order_transaction_collection';
    }

    protected function getExpectedClass(): string
    {
        return PaymentOrderTransactionEntity::class;
    }
}
