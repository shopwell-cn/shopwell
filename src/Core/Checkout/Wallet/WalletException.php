<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Wallet;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;

#[Package('checkout')]
class WalletException extends HttpException
{
}
