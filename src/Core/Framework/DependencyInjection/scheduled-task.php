<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\MessageQueue\Api\ScheduledTaskController;
use Shopwell\Core\Framework\MessageQueue\Command\DeactivateScheduledTaskCommand;
use Shopwell\Core\Framework\MessageQueue\Command\ListScheduledTaskCommand;
use Shopwell\Core\Framework\MessageQueue\Command\RegisterScheduledTasksCommand;
use Shopwell\Core\Framework\MessageQueue\Command\RunSingleScheduledTaskCommand;
use Shopwell\Core\Framework\MessageQueue\Command\ScheduledTaskRunner;
use Shopwell\Core\Framework\MessageQueue\Command\ScheduleScheduledTaskCommand;
use Shopwell\Core\Framework\MessageQueue\ScheduledTask\MessageQueue\RegisterScheduledTaskHandler;
use Shopwell\Core\Framework\MessageQueue\ScheduledTask\Registry\TaskRegistry;
use Shopwell\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskDefinition;
use Shopwell\Core\Framework\MessageQueue\ScheduledTask\Scheduler\TaskRunner;
use Shopwell\Core\Framework\MessageQueue\ScheduledTask\Scheduler\TaskScheduler;
use Shopwell\Core\Framework\MessageQueue\ScheduledTask\SymfonyBridge\ScheduleProvider;
use Shopwell\Core\Framework\MessageQueue\Subscriber\PluginLifecycleSubscriber;
use Shopwell\Core\Framework\MessageQueue\Subscriber\UpdatePostFinishSubscriber;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ScheduledTaskDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(TaskScheduler::class)
        ->args([
            service('scheduled_task.repository'),
            service('messenger.default_bus'),
            service('parameter_bag'),
            '%shopwell.messenger.scheduled_task.requeue_timeout%',
        ]);

    $services->set(TaskRegistry::class)
        ->args([
            tagged_iterator('shopwell.scheduled.task'),
            service('scheduled_task.repository'),
            service('parameter_bag'),
        ]);

    $services->set(ScheduleProvider::class)
        ->args([
            tagged_iterator('shopwell.scheduled.task'),
            service(Connection::class),
            service('cache.object'),
            service('lock.factory'),
        ])
        ->tag('scheduler.schedule_provider', ['name' => 'shopwell']);

    $services->set(RegisterScheduledTaskHandler::class)
        ->args([service(TaskRegistry::class)])
        ->tag('messenger.message_handler');

    $services->set(PluginLifecycleSubscriber::class)
        ->args([
            service(TaskRegistry::class),
            service('cache.messenger.restart_workers_signal'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(TaskRunner::class)
        ->args([
            tagged_iterator('messenger.message_handler'),
            service('scheduled_task.repository'),
        ]);

    $services->set(RegisterScheduledTasksCommand::class)
        ->args([service(TaskRegistry::class)])
        ->tag('console.command');

    $services->set(ScheduledTaskRunner::class)
        ->args([
            service(TaskScheduler::class),
            service('cache.messenger.restart_workers_signal'),
        ])
        ->tag('console.command');

    $services->set(ListScheduledTaskCommand::class)
        ->args([service(TaskRegistry::class)])
        ->tag('console.command');

    $services->set(RunSingleScheduledTaskCommand::class)
        ->args([service(TaskRunner::class)])
        ->tag('console.command');

    $services->set(DeactivateScheduledTaskCommand::class)
        ->args([service(TaskRegistry::class)])
        ->tag('console.command');

    $services->set(ScheduleScheduledTaskCommand::class)
        ->args([service(TaskRegistry::class)])
        ->tag('console.command');

    $services->set(ScheduledTaskController::class)
        ->public()
        ->args([service(TaskScheduler::class)])
        ->call('setContainer', [service('service_container')]);

    $services->set(UpdatePostFinishSubscriber::class)
        ->args([service(TaskRegistry::class)])
        ->tag('kernel.event_subscriber');
};
