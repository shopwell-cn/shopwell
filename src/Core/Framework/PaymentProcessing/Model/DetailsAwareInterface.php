<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Model;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
interface DetailsAwareInterface
{
    public object|array $details {
        set;
    }
}
