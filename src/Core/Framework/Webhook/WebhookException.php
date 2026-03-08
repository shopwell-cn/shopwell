<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Webhook;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class WebhookException extends HttpException
{
    public const string WEBHOOK_FAILED = 'FRAMEWORK__WEBHOOK_FAILED';
    public const string APP_WEBHOOK_FAILED = 'FRAMEWORK__APP_WEBHOOK_FAILED';
    public const string INVALID_DATA_MAPPING = 'FRAMEWORK__WEBHOOK_INVALID_DATA_MAPPING';
    public const string UNKNOWN_DATA_TYPE = 'FRAMEWORK__WEBHOOK_UNKNOWN_DATA_TYPE';

    public static function webhookFailedException(string $webhookId, \Throwable $e): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::WEBHOOK_FAILED,
            'Webhook "{{ webhookId }}" failed with error: {{ error }}.',
            ['webhookId' => $webhookId, 'error' => $e->getMessage()],
            $e
        );
    }

    public static function appWebhookFailedException(string $webhookId, string $appId, \Throwable $e): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::APP_WEBHOOK_FAILED,
            'Webhook "{{ webhookId }}" from "{{ appId }}" failed with error: {{ error }}.',
            ['webhookId' => $webhookId, 'appId' => $appId, 'error' => $e->getMessage()],
            $e
        );
    }

    public static function invalidDataMapping(string $propertyName, string $className): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_DATA_MAPPING,
            'Invalid available DataMapping, could not get property "{{ propertyName }}" on instance of {{ class }}',
            ['propertyName' => $propertyName, 'class' => $className]
        );
    }

    public static function unknownEventDataType(string $type): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::UNKNOWN_DATA_TYPE,
            'Unknown EventDataType: {{ type }}',
            ['type' => $type]
        );
    }
}
