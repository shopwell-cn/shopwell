<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;
use Shopwell\Core\PaymentSystem\Gateway\Exception\RequestNotSupportedException;
use Symfony\Component\HttpFoundation\Response;

#[Package('payment-system')]
class PaymentSystemGatewayException extends HttpException
{
    final public const string REQUEST_NOT_SUPPORTED_EXCEPTION = 'PAYMENT_SYSTEM__FRAMEWORK_REQUEST_NOT_SUPPORTED';

    public static function requestNotSupported(Struct $request): self
    {
        return new RequestNotSupportedException(
            Response::HTTP_NOT_FOUND,
            self::REQUEST_NOT_SUPPORTED_EXCEPTION,
            'The request class "{{ request }}" is not supported by the payment system framework.',
            ['request' => $request::class]
        );
    }
}
