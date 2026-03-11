<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;
use Shopwell\Core\PaymentSystem\Gateway\Exception\PossibleEndlessCycleException;
use Shopwell\Core\PaymentSystem\Gateway\Exception\RequestNotSupportedException;
use Symfony\Component\HttpFoundation\Response;

#[Package('payment-system')]
class PaymentSystemGatewayException extends HttpException
{
    final public const string REQUEST_NOT_SUPPORTED_EXCEPTION = 'PAYMENT_SYSTEM__FRAMEWORK_REQUEST_NOT_SUPPORTED';
    final public const string POSSIBLE_ENDLESS_CYCLE_EXCEPTION = 'PAYMENT_SYSTEM__POSSIBLE_ENDLESS_CYCLE';
    final public const string ACTION_SERVICE_NOT_FOUND = 'PAYMENT_SYSTEM__ACTION_SERVICE_NOT_FOUND';
    final public const string INVALID_ACTION_SERVICE = 'PAYMENT_SYSTEM__INVALID_ACTION_SERVICE';
    final public const string EXTENSION_SERVICE_NOT_FOUND = 'PAYMENT_SYSTEM__EXTENSION_SERVICE_NOT_FOUND';
    final public const string INVALID_EXTENSION_SERVICE = 'PAYMENT_SYSTEM__INVALID_EXTENSION_SERVICE';
    final public const string GATEWAY_FACTORY_NOT_FOUND = 'PAYMENT_SYSTEM__GATEWAY_FACTORY_NOT_FOUND';
    final public const string GATEWAY_NOT_FOUND = 'PAYMENT_SYSTEM__GATEWAY_NOT_FOUND';

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

    public static function actionServiceNotFound(string $serviceId): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::ACTION_SERVICE_NOT_FOUND,
            'Action service "{{ serviceId }}" not found in container.',
            ['serviceId' => $serviceId]
        );
    }

    public static function invalidAction(string $serviceId, string $expectedType): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_ACTION_SERVICE,
            'Action service "{{ serviceId }}" must implement {{ expectedType }}.',
            ['serviceId' => $serviceId, 'expectedType' => $expectedType]
        );
    }

    public static function extensionServiceNotFound(string $serviceId): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::EXTENSION_SERVICE_NOT_FOUND,
            'Extension service "{{ serviceId }}" not found in container.',
            ['serviceId' => $serviceId]
        );
    }

    public static function invalidExtension(string $serviceId, string $expectedType): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_EXTENSION_SERVICE,
            'Extension service "{{ serviceId }}" must implement {{ expectedType }}.',
            ['serviceId' => $serviceId, 'expectedType' => $expectedType]
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
