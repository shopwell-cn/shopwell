<?php declare(strict_types=1);

namespace Shopwell\Core\Service\Subscriber;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Service\Event\NewServicesInstalledEvent;
use Shopwell\Core\Service\Event\ServiceInstalledEvent;
use Shopwell\Core\Service\Event\ServiceUpdatedEvent;
use Shopwell\Core\Service\LifecycleManager;
use Shopwell\Core\Service\Notification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
readonly class ServiceLifecycleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LifecycleManager $lifecycleManager,
        private Notification $notification,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ServiceInstalledEvent::class => 'syncState',
            ServiceUpdatedEvent::class => 'syncState',
            NewServicesInstalledEvent::class => 'sendInstalledNotification',
        ];
    }

    public function syncState(ServiceInstalledEvent|ServiceUpdatedEvent $event): void
    {
        $this->lifecycleManager->syncState($event->service, $event->getContext());
    }

    public function sendInstalledNotification(NewServicesInstalledEvent $event): void
    {
        $this->notification->newServicesInstalled();
    }
}
