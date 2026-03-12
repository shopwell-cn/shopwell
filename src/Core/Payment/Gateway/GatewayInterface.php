<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Payment\Gateway\Exception\ReplyException;

#[Package('payment-system')]
interface GatewayInterface
{
    public function execute(mixed $request, bool $catchReply = false): ?ReplyException;
}
