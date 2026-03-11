<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api\Exception;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
class InvalidParamException extends HttpException
{
}
