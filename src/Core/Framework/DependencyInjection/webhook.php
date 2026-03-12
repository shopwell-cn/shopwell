<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Api\Serializer\JsonEntityEncoder;
use Shopwell\Core\Framework\App\AppLocaleProvider;
use Shopwell\Core\Framework\App\Payload\AppPayloadServiceHelper;
use Shopwell\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopwell\Core\Framework\Event\BusinessEventCollector;
use Shopwell\Core\Framework\Webhook\BusinessEventEncoder;
use Shopwell\Core\Framework\Webhook\EventLog\WebhookEventLogDefinition;
use Shopwell\Core\Framework\Webhook\Handler\WebhookEventMessageHandler;
use Shopwell\Core\Framework\Webhook\Hookable\HookableEventCollector;
use Shopwell\Core\Framework\Webhook\Hookable\HookableEventFactory;
use Shopwell\Core\Framework\Webhook\Hookable\WriteResultMerger;
use Shopwell\Core\Framework\Webhook\ScheduledTask\CleanupWebhookEventLogTask;
use Shopwell\Core\Framework\Webhook\ScheduledTask\CleanupWebhookEventLogTaskHandler;
use Shopwell\Core\Framework\Webhook\Service\RelatedWebhooks;
use Shopwell\Core\Framework\Webhook\Service\WebhookCleanup;
use Shopwell\Core\Framework\Webhook\Service\WebhookLoader;
use Shopwell\Core\Framework\Webhook\Service\WebhookManager;
use Shopwell\Core\Framework\Webhook\Subscriber\RetryWebhookMessageFailedSubscriber;
use Shopwell\Core\Framework\Webhook\WebhookCacheClearer;
use Shopwell\Core\Framework\Webhook\WebhookDefinition;
use Shopwell\Core\Framework\Webhook\WebhookDispatcher;
use Shopwell\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Messenger\MessageBusInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(WebhookDispatcher::class)
        ->decorate('event_dispatcher', null, 100)
        ->args([
            service(WebhookDispatcher::class . '.inner'),
            service(WebhookManager::class),
        ]);

    $services->set(WebhookLoader::class)
        ->args([service(Connection::class)]);

    $services->set(WebhookManager::class)
        ->lazy()
        ->args([
            service(WebhookLoader::class),
            service('event_dispatcher'),
            service(Connection::class),
            service(HookableEventFactory::class),
            service(AppLocaleProvider::class),
            service(AppPayloadServiceHelper::class),
            service('shopwell.app_system.guzzle'),
            service(MessageBusInterface::class),
            '%env(APP_URL)%',
            '%kernel.shopwell_version%',
            '%shopwell.admin_worker.enable_admin_worker%',
        ]);

    $services->set(WebhookCacheClearer::class)
        ->args([service(WebhookManager::class)])
        ->tag('kernel.event_subscriber')
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(HookableEventFactory::class)
        ->lazy()
        ->args([
            service(BusinessEventEncoder::class),
            service(WriteResultMerger::class),
            service(HookableEventCollector::class),
        ]);

    $services->set(WriteResultMerger::class)
        ->args([service(DefinitionInstanceRegistry::class)]);

    $services->set(BusinessEventEncoder::class)
        ->args([
            service(JsonEntityEncoder::class),
            service(DefinitionInstanceRegistry::class),
        ]);

    $services->set(WebhookDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(WebhookEventLogDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(RelatedWebhooks::class)
        ->args([service(Connection::class)]);

    $services->set(HookableEventCollector::class)
        ->args([
            service(BusinessEventCollector::class),
            service(DefinitionInstanceRegistry::class),
            tagged_iterator('shopwell.entity.hookable'),
        ]);

    $services->set(WebhookEventMessageHandler::class)
        ->args([
            service('shopwell.app_system.guzzle'),
            service('webhook_event_log.repository'),
            service(RelatedWebhooks::class),
        ])
        ->tag('messenger.message_handler');

    $services->set(RetryWebhookMessageFailedSubscriber::class)
        ->args([
            service(Connection::class),
            service(RelatedWebhooks::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(WebhookCleanup::class)
        ->args([
            service(SystemConfigService::class),
            service(Connection::class),
        ]);

    $services->set(CleanupWebhookEventLogTask::class)
        ->tag('shopwell.scheduled.task');

    $services->set(CleanupWebhookEventLogTaskHandler::class)
        ->args([
            service('scheduled_task.repository'),
            service('logger'),
            service(WebhookCleanup::class),
        ])
        ->tag('messenger.message_handler');
};
