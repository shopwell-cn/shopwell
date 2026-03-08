<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Seo\Exception;

use Shopwell\Core\Content\Seo\SeoException;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('inventory')]
class SeoUrlRouteNotFoundException extends SeoException
{
    public function __construct(string $routeName)
    {
        $errorCode = self::SEO_URL_ROUTE_NOT_FOUND;

        parent::__construct(
            Response::HTTP_NOT_FOUND,
            $errorCode,
            self::$couldNotFindMessage,
            ['entity' => 'SEO URL route', 'field' => 'name', 'value' => $routeName]
        );
    }
}
