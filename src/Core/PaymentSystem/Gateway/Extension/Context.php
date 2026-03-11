<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Extension;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;
use Shopwell\Core\PaymentSystem\Gateway\Action\ActionInterface;
use Shopwell\Core\PaymentSystem\Gateway\Exception\ReplyException;
use Shopwell\Core\PaymentSystem\Gateway\GatewayInterface;

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
