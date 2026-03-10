<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
class PaymentSystemApiException extends HttpException
{
}
