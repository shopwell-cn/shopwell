<?php declare(strict_types=1);

namespace Shopwell\Storefront\Event;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
class MaintenanceRedirectEvent extends StorefrontRedirectEvent
{
}
