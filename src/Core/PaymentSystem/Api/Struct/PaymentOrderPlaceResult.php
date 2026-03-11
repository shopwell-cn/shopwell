<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api\Struct;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;

#[Package('payment-system')]
class PaymentOrderPlaceResult extends Struct
{
    public function __construct(public string $orderId)
    {
    }
}
