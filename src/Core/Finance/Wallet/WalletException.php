<?php declare(strict_types=1);

namespace Shopwell\Core\Finance\Wallet;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;

#[Package('fundamentals@framework')]
class WalletException extends HttpException
{
}
