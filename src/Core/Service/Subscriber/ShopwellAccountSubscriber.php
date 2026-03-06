<?php declare(strict_types=1);

namespace Shopwell\Core\Service\Subscriber;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Store\Event\ShopwellAccountLoginEvent;
use Shopwell\Core\Framework\Store\Event\ShopwellAccountLogoutEvent;
use Shopwell\Core\Service\LifecycleManager;
use Shopwell\Core\Service\Requirement\ShopwellAccountRequirement;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
readonly class ShopwellAccountSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LifecycleManager $lifecycleManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ShopwellAccountLoginEvent::class => 'syncAccountRequirement',
            ShopwellAccountLogoutEvent::class => 'syncAccountRequirement',
        ];
    }

    public function syncAccountRequirement(ShopwellAccountLoginEvent|ShopwellAccountLogoutEvent $event): void
    {
        $this->lifecycleManager->syncRequirement(
            ShopwellAccountRequirement::NAME,
            $event->getContext()
        );
    }
}
