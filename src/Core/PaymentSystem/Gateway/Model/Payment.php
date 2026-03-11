<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Model;

use Shopwell\Core\PaymentSystem\Order\PaymentOrderEntity;

class Payment implements PaymentInterface
{
    public PaymentOrderEntity $order;

    public object|array $details = [];

    public function __construct(PaymentOrderEntity $order)
    {
        $this->order = $order;
    }
}
