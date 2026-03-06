<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Newsletter\ScheduledTask;

use Psr\Log\LoggerInterface;
use Shopwell\Core\Content\Newsletter\Aggregate\NewsletterRecipient\NewsletterRecipientCollection;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use Shopwell\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler(handles: NewsletterRecipientTask::class)]
#[Package('after-sales')]
final class NewsletterRecipientTaskHandler extends ScheduledTaskHandler
{
    /**
     * @internal
     *
     * @param EntityRepository<ScheduledTaskCollection> $scheduledTaskRepository
     * @param EntityRepository<NewsletterRecipientCollection> $newsletterRecipientRepository
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $logger,
        private readonly EntityRepository $newsletterRecipientRepository
    ) {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        $context = Context::createCLIContext();

        $criteria = $this->getExpiredNewsletterRecipientCriteria();
        $emailRecipient = $this->newsletterRecipientRepository->searchIds($criteria, $context);

        if ($emailRecipient->getIds() === []) {
            return;
        }

        $emailRecipientIds = array_map(fn ($id) => ['id' => $id], $emailRecipient->getIds());

        $this->newsletterRecipientRepository->delete($emailRecipientIds, $context);
    }

    private function getExpiredNewsletterRecipientCriteria(): Criteria
    {
        $criteria = new Criteria();

        $dateTime = (new \DateTime())->add(\DateInterval::createFromDateString('-30 days'));

        $criteria->addFilter(new RangeFilter(
            'createdAt',
            [
                RangeFilter::LTE => $dateTime->format(\DATE_ATOM),
            ]
        ));

        $criteria->addFilter(new EqualsFilter('status', 'notSet'));

        $criteria->setLimit(999);

        return $criteria;
    }
}
