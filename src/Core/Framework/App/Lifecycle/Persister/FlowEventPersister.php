<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\Lifecycle\Persister;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\App\Aggregate\FlowEvent\AppFlowEventCollection;
use Shopwell\Core\Framework\App\Flow\Event\Event;
use Shopwell\Core\Framework\App\Lifecycle\AppLifecycleContext;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Util\Filesystem;
use Shopwell\Core\Framework\Uuid\Uuid;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class FlowEventPersister implements PersisterInterface
{
    /**
     * @param EntityRepository<AppFlowEventCollection> $flowEventsRepository
     */
    public function __construct(
        private readonly EntityRepository $flowEventsRepository,
        private readonly Connection $connection
    ) {
    }

    public function persist(AppLifecycleContext $context): void
    {
        $flowEvents = $this->getFlowEvents($context->appFilesystem);

        if ($flowEvents) {
            $this->updateEvents($flowEvents, $context->app->getId(), $context->context, $context->defaultLocale);
        }
    }

    public function updateEvents(Event $flowEvent, string $appId, Context $context, string $defaultLocale): void
    {
        $existingFlowEvents = $this->connection->fetchAllKeyValue('SELECT name, LOWER(HEX(id)) FROM app_flow_event WHERE app_id = :appId;', [
            'appId' => Uuid::fromHexToBytes($appId),
        ]);

        $flowEvents = $flowEvent->getCustomEvents()?->getCustomEvents() ?? [];
        $upserts = [];
        foreach ($flowEvents as $event) {
            $payload = array_merge([
                'appId' => $appId,
            ], $event->toArray($defaultLocale));

            $existing = $existingFlowEvents[$event->getName()] ?? null;
            if ($existing) {
                $payload['id'] = $existing;
                unset($existingFlowEvents[$event->getName()]);
            }

            $upserts[] = $payload;
        }

        if ($upserts !== []) {
            $this->flowEventsRepository->upsert($upserts, $context);
        }

        $this->deleteOldAppFlowEvents($existingFlowEvents, $context);
    }

    public function deactivateFlow(string $appId): void
    {
        $this->connection->executeStatement(
            'UPDATE `flow` SET `active` = false WHERE `event_name` IN (SELECT `name` FROM `app_flow_event` WHERE `app_id` = :appId);',
            [
                'appId' => Uuid::fromHexToBytes($appId),
            ],
        );
    }

    private function getFlowEvents(Filesystem $fs): ?Event
    {
        if (!$fs->has('Resources/flow.xml')) {
            return null;
        }

        return Event::createFromXmlFile($fs->path('Resources/flow.xml'));
    }

    /**
     * @param array<int|string, mixed> $toBeRemoved
     */
    private function deleteOldAppFlowEvents(array $toBeRemoved, Context $context): void
    {
        $ids = array_values($toBeRemoved);

        if ($ids === []) {
            return;
        }

        $ids = array_map(static function (string $id): array {
            return ['id' => $id];
        }, $ids);

        $this->flowEventsRepository->delete($ids, $context);
    }
}
