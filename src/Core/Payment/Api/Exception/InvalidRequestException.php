<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Api\Exception;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Payment\Api\PaymentApiException;

#[Package('payment-system')]
class InvalidRequestException extends PaymentApiException
{
}
