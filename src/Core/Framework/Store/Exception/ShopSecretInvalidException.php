<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Store\Exception;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\ShopwellHttpException;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class ShopSecretInvalidException extends ShopwellHttpException
{
    public function __construct()
    {
        parent::__construct('Store shop secret is invalid');
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__STORE_SHOP_SECRET_INVALID';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
