<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway\Security;

use Shopwell\Core\Payment\Gateway\DataAbstractionLayer\PaymentTokenEntity;

interface TokenAggregateInterface
{
    public ?PaymentTokenEntity $token {
        get;
    }
}
