<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api\Exception;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\PaymentSystem\Api\PaymentSystemApiException;

#[Package('payment-system')]
class InvalidRequestException extends PaymentSystemApiException
{
}
