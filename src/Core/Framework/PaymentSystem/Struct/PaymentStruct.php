<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentSystem\Struct;

use Payum\Core\Model\Payment;
use Shopwell\Core\Framework\Log\Package;

/**
 * @method array getDetails()
 */
#[Package('framework')]
class PaymentStruct extends Payment
{
}
