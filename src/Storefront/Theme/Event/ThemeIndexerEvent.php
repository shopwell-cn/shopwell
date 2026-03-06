<?php declare(strict_types=1);

namespace Shopwell\Storefront\Theme\Event;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Event\NestedEvent;
use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
class ThemeIndexerEvent extends NestedEvent
{
    public function __construct(
        private readonly array $ids,
        private readonly Context $context,
        private readonly array $skip = []
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

    public function getSkip(): array
    {
        return $this->skip;
    }
}
