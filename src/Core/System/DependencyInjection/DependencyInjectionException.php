<?php declare(strict_types=1);

namespace Shopwell\Core\System\DependencyInjection;

use Shopwell\Core\Framework\DataAbstractionLayer\Exception\DefinitionNotFoundException;
use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
class DependencyInjectionException extends HttpException
{
    public const string NUMBER_RANGE_REDIS_NOT_CONFIGURED = 'SYSTEM__NUMBER_RANGE_REDIS_NOT_CONFIGURED';

    public static function redisNotConfiguredForNumberRangeIncrementer(): self
    {
        return new self(
            500,
            self::NUMBER_RANGE_REDIS_NOT_CONFIGURED,
            'Parameter "shopwell.number_range.config.connection" is required for redis storage'
        );
    }

    public static function definitionNotFound(string $entity): DefinitionNotFoundException
    {
        return new DefinitionNotFoundException($entity);
    }
}
