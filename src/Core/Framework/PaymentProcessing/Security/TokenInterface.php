<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Security;

interface TokenInterface
{
    public string $token {get; }
}
