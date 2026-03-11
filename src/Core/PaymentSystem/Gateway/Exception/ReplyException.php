<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Exception;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\PaymentSystem\Gateway\PaymentSystemGatewayException;

#[Package('payment-system')]
class ReplyException extends PaymentSystemGatewayException
{
}
