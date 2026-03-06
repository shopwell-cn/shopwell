<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Store\Exception;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\ShopwellHttpException;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class StoreTokenMissingException extends ShopwellHttpException
{
    public function __construct()
    {
        parent::__construct('Store token is missing');
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__STORE_TOKEN_IS_MISSING';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
