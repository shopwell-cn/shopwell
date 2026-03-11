<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;
use Shopwell\Core\PaymentSystem\Gateway\Exception\ReplyException;

#[Package('payment-system')]
interface GatewayInterface
{
    public function execute(Struct $request, bool $catchReply = false): ?ReplyException;
}
