<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Event;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\PaymentSystem\Gateway\Extension\Context;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('payment-system')]
class ExecuteEvent extends Event
{
    protected Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }
}
