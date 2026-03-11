<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Security;

use Shopwell\Core\PaymentSystem\Gateway\DataAbstractionLayer\PaymentTokenEntity;

interface TokenAggregateInterface
{
    public ?PaymentTokenEntity $token {
        get;
    }
}
