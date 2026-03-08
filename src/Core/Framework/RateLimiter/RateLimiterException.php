<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\RateLimiter;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\RateLimiter\Exception\RateLimitExceededException;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class RateLimiterException extends HttpException
{
    public const string RATE_LIMIT_EXCEEDED = 'FRAMEWORK__RATE_LIMIT_EXCEEDED';
    public const string FACTORY_NOT_FOUND = 'FRAMEWORK__RATE_LIMITER_FACTORY_NOT_FOUND';

    public static function limitExceeded(int $retryAfter, ?\Throwable $e = null): self
    {
        return new RateLimitExceededException($retryAfter, $e);
    }

    public static function factoryNotFound(string $route): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::FACTORY_NOT_FOUND,
            'Rate limiter factory for route "{{ route }}" not found.',
            ['route' => $route]
        );
    }
}
