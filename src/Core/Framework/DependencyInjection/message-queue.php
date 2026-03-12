<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Adapter\Doctrine\Messenger\DoctrineTransportFactory;
use Shopwell\Core\Framework\Adapter\Messenger\Middleware\QueuedTimeMiddleware;
use Shopwell\Core\Framework\MessageQueue\Api\ConsumeMessagesController;
use Shopwell\Core\Framework\MessageQueue\Middleware\RoutingOverwriteMiddleware;
use Shopwell\Core\Framework\MessageQueue\SendEmailMessageJsonSerializer;
use Shopwell\Core\Framework\MessageQueue\Service\MessageSizeCalculator;
use Shopwell\Core\Framework\MessageQueue\Stats\MySQLStatsRepository;
use Shopwell\Core\Framework\MessageQueue\Stats\StatsService;
use Shopwell\Core\Framework\MessageQueue\Subscriber\EarlyReturnMessagesListener;
use Shopwell\Core\Framework\MessageQueue\Subscriber\MessageQueueSizeRestrictListener;
use Shopwell\Core\Framework\MessageQueue\Subscriber\MessageQueueStatsSubscriber;
use Shopwell\Core\Framework\MessageQueue\Telemetry\MessageQueueTelemetrySubscriber;
use Shopwell\Core\Framework\Telemetry\Metrics\Meter;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(EarlyReturnMessagesListener::class);

    $services->set(MessageQueueSizeRestrictListener::class)
        ->args([
            service(MessageSizeCalculator::class),
            '%shopwell.messenger.enforce_message_size%',
            '%shopwell.messenger.message_max_kib_size%',
        ])
        ->tag('kernel.event_listener', ['event' => SendMessageToTransportsEvent::class]);

    $services->set(MessageQueueStatsSubscriber::class)
        ->args([service(StatsService::class)])
        ->tag('kernel.event_subscriber');

    $services->set(MessageQueueTelemetrySubscriber::class)
        ->args([
            service(Meter::class),
            service(MessageSizeCalculator::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(ConsumeMessagesController::class)
        ->public()
        ->args([
            service('messenger.receiver_locator'),
            service('messenger.default_bus'),
            service('messenger.listener.stop_worker_on_restart_signal_listener'),
            service(EarlyReturnMessagesListener::class),
            service(MessageQueueStatsSubscriber::class),
            '%messenger.default_transport_name%',
            '%shopwell.admin_worker.memory_limit%',
            '%shopwell.admin_worker.poll_interval%',
            service('lock.factory'),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set('messenger.transport.doctrine.factory', DoctrineTransportFactory::class)
        ->args([service(Connection::class)])
        ->tag('messenger.transport_factory');

    $services->set(SendEmailMessageJsonSerializer::class)
        ->tag('serializer.normalizer');

    $services->set(MessageSizeCalculator::class)
        ->args([service('messenger.default_serializer')]);

    $services->set(RoutingOverwriteMiddleware::class)
        ->args(['%shopwell.messenger.routing_overwrite%']);

    $services->set(MySQLStatsRepository::class)
        ->args([
            service(Connection::class),
            '%shopwell.messenger.stats.time_span%',
        ]);

    $services->set(StatsService::class)
        ->args([
            service(MySQLStatsRepository::class),
            '%shopwell.messenger.stats.enabled%',
        ]);

    $services->set(QueuedTimeMiddleware::class);
};
