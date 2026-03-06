<?php declare(strict_types=1);

namespace Shopwell\Core\Service\Subscriber;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Service\Event\PermissionsGrantedEvent;
use Shopwell\Core\Service\Event\PermissionsRevokedEvent;
use Shopwell\Core\Service\LifecycleManager;
use Shopwell\Core\Service\Requirement\ServiceConsentRequirement;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
readonly class PermissionsSubscriber implements EventSubscriberInterface
{
    public function __construct(private LifecycleManager $manager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PermissionsGrantedEvent::class => 'syncConsentRequirement',
            PermissionsRevokedEvent::class => 'syncConsentRequirement',
        ];
    }

    public function syncConsentRequirement(PermissionsGrantedEvent|PermissionsRevokedEvent $event): void
    {
        $this->manager->syncRequirement(
            ServiceConsentRequirement::NAME,
            $event->getContext()
        );
    }
}
