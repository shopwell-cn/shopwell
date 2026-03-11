<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api\Response;
use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
class PaymentResponse extends AbstractApiResponse
{
    public string $paymentOrderNumber;
    public string $outOrderNo;
}
