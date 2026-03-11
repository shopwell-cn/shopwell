<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Request;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\PaymentSystem\Gateway\DataAbstractionLayer\PaymentTokenEntity;

#[Package('payment-system')]
class Convert
{
    public mixed $result;

    public function __construct(
        public mixed $source,
        public string $to,
        public ?PaymentTokenEntity $paymentToken = null
    ) {
    }
}
