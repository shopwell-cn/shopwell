<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Extension;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\Action\ActionInterface;
use Shopwell\Core\Framework\PaymentProcessing\Exception\ReplyException;
use Shopwell\Core\Framework\PaymentProcessing\GatewayInterface;
use Shopwell\Core\Framework\Struct\Struct;

#[Package('payment-system')]
class Context
{
    public ?ActionInterface $action = null;

    public ?\Exception $exception = null;

    public ?ReplyException $reply = null;

    /**
     * @param Context[] $previous
     */
    public function __construct(
        public GatewayInterface $gateway,
        public Struct $request,
        public array $previous,
    ) {
    }
}
