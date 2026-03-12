<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway\Exception;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Payment\Gateway\PaymentGatewayException;

#[Package('payment-system')]
class RequestNotSupportedException extends PaymentGatewayException
{
}
