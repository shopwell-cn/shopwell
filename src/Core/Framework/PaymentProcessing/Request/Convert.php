<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Request;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\Security\TokenInterface;

#[Package('payment-system')]
class Convert
{
    public mixed $result;

    public function __construct(
        public mixed $source,
        public string $to,
        public ?TokenInterface $paymentToken = null
    ) {
    }
}
