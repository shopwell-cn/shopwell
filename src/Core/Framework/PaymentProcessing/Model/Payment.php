<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Model;

class Payment implements PaymentInterface
{
    public object|array $details = [];
}
