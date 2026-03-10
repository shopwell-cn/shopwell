<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api\Struct;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;

#[Package('payment-system')]
abstract class ApiStruct extends Struct
{
    /**
     * @param array<string, mixed> $data
     */
    abstract public static function fromArray(array $data): self;
}
