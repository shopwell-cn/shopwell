<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\Exception\PossibleEndlessCycleException;
use Shopwell\Core\Framework\PaymentProcessing\Exception\RequestNotSupportedException;
use Shopwell\Core\Framework\Struct\Struct;
use Symfony\Component\HttpFoundation\Response;

#[Package('payment-system')]
class PaymentGatewayException extends HttpException
{
    final public const string REQUEST_NOT_SUPPORTED_EXCEPTION = 'PAYMENT__FRAMEWORK_REQUEST_NOT_SUPPORTED';
    final public const string POSSIBLE_ENDLESS_CYCLE_EXCEPTION = 'PAYMENT__POSSIBLE_ENDLESS_CYCLE';
    final public const string GATEWAY_FACTORY_NOT_FOUND = 'PAYMENT__GATEWAY_FACTORY_NOT_FOUND';
    final public const string GATEWAY_NOT_FOUND = 'PAYMENT__GATEWAY_NOT_FOUND';

    public static function requestNotSupported(Struct $request): self
    {
        return new RequestNotSupportedException(
            Response::HTTP_NOT_FOUND,
            self::REQUEST_NOT_SUPPORTED_EXCEPTION,
            'The request class "{{ request }}" is not supported by the payment system framework.',
            ['request' => $request::class]
        );
    }

    public static function possibleEndlessCycle(int $count): self
    {
        return new PossibleEndlessCycleException(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::POSSIBLE_ENDLESS_CYCLE_EXCEPTION,
            'Possible endless cycle detected. ::onPreExecute was called {{ count }} times before reaching the limit.',
            ['count' => $count]
        );
    }

    public static function gatewayFactoryNotFound(string $name): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::GATEWAY_FACTORY_NOT_FOUND,
            'Gateway factory "{{ name }}" does not exist.',
            ['name' => $name]
        );
    }

    public static function gatewayNotFound(string $name): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::GATEWAY_NOT_FOUND,
            'Gateway "{{ name }}" does not exist.',
            ['name' => $name]
        );
    }
}
