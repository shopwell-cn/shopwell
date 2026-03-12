<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway\Model;

use Shopwell\Core\Payment\Order\PaymentOrderEntity;

class Payment implements PaymentInterface
{
    public PaymentOrderEntity $order;

    public object|array $details = [];

    public function __construct(PaymentOrderEntity $order)
    {
        $this->order = $order;
    }
}
