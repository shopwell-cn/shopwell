<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api\Struct;

use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
class PaymentOrderStruct extends ApiStruct
{
    public float $amount;

    /**
     * @return PaymentOrderStruct
     */
    public static function fromArray(array $data): ApiStruct
    {
        return new self();
    }
}
