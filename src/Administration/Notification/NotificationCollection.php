<?php declare(strict_types=1);

namespace Shopwell\Administration\Notification;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @deprecated tag:v6.8.0 - Will be removed in 6.8.0. Use Shopwell\Core\Framework\Notification\NotificationCollection instead
 *
 * @extends EntityCollection<NotificationEntity>
 */
#[Package('framework')]
class NotificationCollection extends EntityCollection
{
}
