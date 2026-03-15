<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Points\Subscriber;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Checkout\Customer\CustomerEvents;
use Shopwell\Core\Defaults;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopwell\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
readonly class CustomerPointsSubscriber implements EventSubscriberInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CustomerEvents::CUSTOMER_WRITTEN_EVENT => 'onCustomerWritten',
        ];
    }

    public function onCustomerWritten(EntityWrittenEvent $event): void
    {
        foreach ($event->getWriteResults() as $writeResult) {
            if ($writeResult->getOperation() !== EntityWriteResult::OPERATION_INSERT) {
                continue;
            }
            $payload = $writeResult->getPayload();
            if (!isset($payload['id'])) {
                continue;
            }
            $this->createCustomerPoints($payload['id']);
        }
    }

    private function createCustomerPoints(string $customerId): void
    {
        $this->connection->insert('points', [
            'id' => Uuid::randomBytes(),
            'referenced_id' => $customerId,
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }
}
