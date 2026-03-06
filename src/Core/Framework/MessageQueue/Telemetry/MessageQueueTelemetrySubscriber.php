<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\MessageQueue\Telemetry;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\MessageQueue\Service\MessageSizeCalculator;
use Shopwell\Core\Framework\Telemetry\Metrics\Meter;
use Shopwell\Core\Framework\Telemetry\Metrics\Metric\ConfiguredMetric;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/**
 * @internal
 */
#[Package('framework')]
class MessageQueueTelemetrySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Meter $meter,
        private readonly MessageSizeCalculator $messageSizeCalculator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => 'emitMessageSizeMetric',
        ];
    }

    public function emitMessageSizeMetric(WorkerMessageReceivedEvent $event): void
    {
        $this->meter->emit(new ConfiguredMetric(
            name: 'messenger.message.size',
            value: $this->messageSizeCalculator->size($event->getEnvelope()),
        ));
    }
}
