<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Event;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\Extension\Context;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class ExecuteEvent extends Event
{
    protected Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }
}
