<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Framework;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
class PaymentSystemFrameworkException extends HttpException
{
}
