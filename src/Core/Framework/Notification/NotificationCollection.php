<?php declare(strict_types=1); // @phpstan-ignore symplify.multipleClassLikeInFile

namespace Shopwell\Core\Framework\Notification;

use Shopwell\Administration\Notification\NotificationCollection as AdminNotificationCollection;
use Shopwell\Administration\Notification\NotificationEntity as AdminNotificationEntity;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

if (class_exists(AdminNotificationCollection::class)) {
    /**
     * @deprecated tag:v6.8.0 - reason:class-hierarchy-change - Will not extend from `\Shopwell\Administration\Notification\NotificationCollection` and will instead extend directly from `\Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection`.
     *
     * @phpstan-ignore phpat.restrictNamespacesInCore (Don't do that! This will be fixed with the next major version as it is not used anymore)
     */
    #[Package('framework')]
    class NotificationCollection extends AdminNotificationCollection
    {
        protected function getExpectedClass(): string
        {
            /** @phpstan-ignore phpat.restrictNamespacesInCore */
            return AdminNotificationEntity::class;
        }
    }
} else {
    /**
     * @extends EntityCollection<NotificationEntity>
     */
    #[Package('framework')]
    class NotificationCollection extends EntityCollection
    {
        protected function getExpectedClass(): string
        {
            return NotificationEntity::class;
        }
    }
}
