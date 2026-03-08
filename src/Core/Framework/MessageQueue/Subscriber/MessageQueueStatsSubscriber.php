<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\MessageQueue\Subscriber;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\MessageQueue\Stats\StatsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

/**
 * @internal
 */
#[Package('framework')]
class MessageQueueStatsSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly StatsService $statsService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageHandledEvent::class => 'onMessageHandled',
        ];
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        $this->statsService->registerMessage($event->getEnvelope());
    }
}
