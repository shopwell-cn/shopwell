<?php declare(strict_types=1);

namespace Shopwell\Administration\Controller;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Routing\ApiRouteScope;
use Shopwell\Core\PlatformRequest;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @deprecated tag:v6.8.0 - Will be removed in 6.8.0. Use Shopwell\Core\Framework\Notification\Api\NotificationController instead
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('framework')]
class NotificationController extends \Shopwell\Core\Framework\Notification\Api\NotificationController
{
}
