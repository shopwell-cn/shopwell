<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Gateway\Command;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Collection;

/**
 * @extends Collection<AbstractCheckoutGatewayCommand>
 */
#[Package('checkout')]
class CheckoutGatewayCommandCollection extends Collection
{
}
