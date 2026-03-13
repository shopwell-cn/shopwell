<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\DocumentGenerator;

use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
class Counter
{
    public private(set) int $counter = 0;

    public function increment(): void
    {
        ++$this->counter;
    }
}
