<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Flow\Events;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\Event\GenericEvent;
use Shopwell\Core\Framework\Event\ShopwellEvent;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('after-sales')]
class BeforeLoadStorableFlowDataEvent extends Event implements ShopwellEvent, GenericEvent
{
    public function __construct(
        private readonly string $entityName,
        private readonly Criteria $criteria,
        private readonly Context $context,
    ) {
    }

    public function getName(): string
    {
        return 'flow.storer.' . $this->entityName . '.criteria.event';
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
