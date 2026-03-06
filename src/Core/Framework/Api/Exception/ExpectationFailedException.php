<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Api\Exception;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\ShopwellHttpException;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class ExpectationFailedException extends ShopwellHttpException
{
    /**
     * @param list<string> $fails
     */
    public function __construct(private readonly array $fails)
    {
        parent::__construct('API Expectations failed');
    }

    /**
     * @return array<string>
     */
    public function getParameters(): array
    {
        return $this->fails;
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__API_EXPECTATION_FAILED';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_EXPECTATION_FAILED;
    }
}
