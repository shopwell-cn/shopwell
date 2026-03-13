<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Exception;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\PaymentProcessingException;

#[Package('framework')]
class RequestNotSupportedException extends PaymentProcessingException
{
}
