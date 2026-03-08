<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Payment\Cart\Recurring;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;

/**
 * This is an experimental payment struct to make generic subscription information available without relying on a payment handler to a specific subscription extensions
 */
#[Package('checkout')]
class RecurringDataStruct extends Struct
{
}
