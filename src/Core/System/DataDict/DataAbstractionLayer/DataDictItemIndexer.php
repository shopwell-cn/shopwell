<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict\DataAbstractionLayer;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopwell\Core\Framework\DataAbstractionLayer\Doctrine\RetryableTransaction;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopwell\Core\Framework\DataAbstractionLayer\Indexing\ChildCountUpdater;
use Shopwell\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopwell\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use Shopwell\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopwell\Core\Framework\DataAbstractionLayer\Indexing\TreeUpdater;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopwell\Core\Framework\Uuid\Uuid;
use Shopwell\Core\System\DataDict\Aggregate\DataDictItem\DataDictItemCollection;
use Shopwell\Core\System\DataDict\Aggregate\DataDictItem\DataDictItemDefinition;
use Shopwell\Core\System\DataDict\Event\DataDictItemIndexerEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[Package('data-services')]
class DataDictItemIndexer extends EntityIndexer
{
    final public const CHILD_COUNT_UPDATER = 'data_dict_item.child-count';
    final public const TREE_UPDATER = 'data_dict_item.tree';

    private const UPDATE_IDS_CHUNK_SIZE = 50;

    /**
     * @param EntityRepository<DataDictItemCollection> $repository
     *
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly IteratorFactory $iteratorFactory,
        private readonly EntityRepository $repository,
        private readonly ChildCountUpdater $childCountUpdater,
        private readonly TreeUpdater $treeUpdater,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function getName(): string
    {
        return 'data_dict_item.indexer';
    }

    public function iterate(?array $offset): ?EntityIndexingMessage
    {
        $iterator = $this->getIterator($offset);

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new DataDictItemIndexingMessage(
            data: array_values($ids),
            offset: $iterator->getOffset()
        );
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $dictEvent = $event->getEventByEntityName(DataDictItemDefinition::ENTITY_NAME);

        if (!$dictEvent) {
            return null;
        }
        $ids = $dictEvent->getIds();
        $idsWithChangedParentIds = [];
        foreach ($dictEvent->getWriteResults() as $result) {
            if (!$result->getExistence()) {
                continue;
            }
            $state = $result->getExistence()->getState();

            if (isset($state['parent_id'])) {
                $ids[] = Uuid::fromBytesToHex($state['parent_id']);
            }

            $payload = $result->getPayload();
            if (\array_key_exists('parentId', $payload)) {
                if ($payload['parentId'] !== null) {
                    $ids[] = $payload['parentId'];
                }
                $idsWithChangedParentIds[] = $payload['id'];
            }
        }

        if (empty($ids)) {
            return null;
        }

        if ($idsWithChangedParentIds !== []) {
            $this->treeUpdater->batchUpdate(
                $idsWithChangedParentIds,
                DataDictItemDefinition::ENTITY_NAME,
                $event->getContext(),
                true
            );
        }

        $children = $this->fetchChildren($ids, $event->getContext()->getVersionId());
        $ids = array_unique(array_merge($ids, $children));

        $chunks = \array_chunk($ids, self::UPDATE_IDS_CHUNK_SIZE);
        $idsForReturnedMessage = array_shift($chunks);

        foreach ($chunks as $chunk) {
            $childrenIndexingMessage = new DataDictItemIndexingMessage($chunk, null, $event->getContext());
            $childrenIndexingMessage->setIndexer($this->getName());
            EntityIndexerRegistry::addSkips($childrenIndexingMessage, $event->getContext());

            $this->messageBus->dispatch($childrenIndexingMessage);
        }

        return new DataDictItemIndexingMessage($idsForReturnedMessage, null, $event->getContext());
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();
        if (!\is_array($ids)) {
            return;
        }

        $ids = array_values(array_unique(array_filter($ids)));
        if (empty($ids)) {
            return;
        }

        $context = $message->getContext();

        RetryableTransaction::retryable($this->connection, function () use ($message, $ids, $context): void {
            if ($message->allow(self::CHILD_COUNT_UPDATER)) {
                // listen to parent id changes
                $this->childCountUpdater->update(DataDictItemDefinition::ENTITY_NAME, $ids, $context);
            }

            if ($message->allow(self::TREE_UPDATER)) {
                $this->treeUpdater->batchUpdate(
                    $ids,
                    DataDictItemDefinition::ENTITY_NAME,
                    $context,
                    !$message->isFullIndexing
                );
            }
        });

        $this->eventDispatcher->dispatch(new DataDictItemIndexerEvent($ids, $context, $message->getSkip(), $message->isFullIndexing));
    }

    public function getTotal(): int
    {
        return $this->getIterator(null)->fetchCount();
    }

    public function getDecorated(): EntityIndexer
    {
        throw new DecorationPatternException(static::class);
    }

    /**
     * @param array<string> $dictIds
     *
     * @return array<string>
     */
    private function fetchChildren(array $dictIds, string $versionId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('DISTINCT LOWER(HEX(dict.id))');
        $query->from('data_dict_item', 'dict');

        $wheres = [];
        foreach ($dictIds as $id) {
            $key = 'path' . $id;
            $wheres[] = 'dict.path LIKE :' . $key;
            $query->setParameter($key, '%|' . $id . '|%');
        }

        $query->andWhere('(' . implode(' OR ', $wheres) . ')');
        $query->setParameter('version', Uuid::fromHexToBytes($versionId));

        return $query->executeQuery()->fetchFirstColumn();
    }

    /**
     * @param array{offset: int|null}|null $offset
     */
    private function getIterator(?array $offset): IterableQuery
    {
        return $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);
    }
}
