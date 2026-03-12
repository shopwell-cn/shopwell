<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Api\Extension;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Extensions\Extension;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Validation\DataBag\DataBag;
use Shopwell\Core\Payment\Api\Struct\PaymentOrderPlaceResult;

/**
 * @codeCoverageIgnore
 *
 * @extends Extension<PaymentOrderPlaceResult>
 */
#[Package('payment-system')]
class PaymentPlaceOrderExtension extends Extension
{
    public const string NAME = 'payment-system.place-order';

    /**
     * @internal
     */
    public function __construct(
        /**
         * @public
         */
        public readonly DataBag $request,
        /**
         * @public
         */
        public readonly Context $context,
    ) {
    }
}
