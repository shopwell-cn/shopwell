<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\Exception\ReplyException;

#[Package('payment-system')]
interface GatewayInterface
{
    public function execute(mixed $request, bool $catchReply = false): ?ReplyException;
}
