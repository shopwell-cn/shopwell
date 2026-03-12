<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Api;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('payment-system')]
class PaymentApiException extends HttpException
{
    public const string PAYMENT_INVALID_ORDER_CODE = 'PAYMENT__INVALID_PAYMENT_ORDER_NOT_STORED';

    public static function invalidPaymentOrderNotStored(string $orderId): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::PAYMENT_INVALID_ORDER_CODE,
            'Order payment failed. The order was not stored.',
            ['orderId' => $orderId]
        );
    }
}
