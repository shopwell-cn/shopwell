<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\PaymentSystem\Gateway\Exception\ReplyException;

#[Package('payment-system')]
interface GatewayInterface
{
    public function execute(mixed $request, bool $catchReply = false): ?ReplyException;
}
