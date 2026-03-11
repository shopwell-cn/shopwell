<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api\Struct;

use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
class PaymentStruct extends AbstractApiStruct
{
    public float $amount;
    public string $outOrderNo;
    public string $currency = 'CNY';
    public string $paymentType;
    public string $subject;
    public string $body;
    public ?string $paymentMethod = null;
    public ?string $notifyUrl = null;
    public ?string $returnUrl = null;
    public ?string $expireTime = null;
    /**
     * @var array<string,mixed>|null
     */
    public ?array $extraParam = null;
    /**
     * @var array<string,mixed>|null
     */
    public ?array $attach = null;
}
