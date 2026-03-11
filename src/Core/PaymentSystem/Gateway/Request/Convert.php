<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Request;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;

#[Package('payment-system')]
class Convert extends Struct
{
    public Struct $result;

    public function __construct(
        public Struct $source,
        public string $to,
    ) {
    }
}
