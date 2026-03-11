<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('payment-system')]
class PaymentSystemApiException extends HttpException
{
    public const string PAYMENT_SYSTEM__INVALID_REQUEST = 'PAYMENT_SYSTEM__INVALID_REQUEST';
    public const string PAYMENT_SYSTEM__PARAM_ERROR = 'PAYMENT_SYSTEM__PARAM_ERROR';
    public const string PAYMENT_SYSTEM_INVALID_ORDER_CODE = 'PAYMENT_SYSTEM__INVALID_PAYMENT_ORDER_NOT_STORED';

    public static function invalidPaymentOrderNotStored(string $orderId): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::PAYMENT_SYSTEM_INVALID_ORDER_CODE,
            'Order payment failed. The order was not stored.',
            ['orderId' => $orderId]
        );
    }
}
