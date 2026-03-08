<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentSystem;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
class PaymentSystemException extends HttpException
{
}
