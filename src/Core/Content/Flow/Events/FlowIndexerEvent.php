<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Flow\Events;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Event\NestedEvent;
use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
class FlowIndexerEvent extends NestedEvent
{
    public function __construct(
        private readonly array $ids,
        private readonly Context $context
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}
