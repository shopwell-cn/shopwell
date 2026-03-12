<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway\Extension;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;
use Shopwell\Core\Payment\Gateway\Action\ActionInterface;
use Shopwell\Core\Payment\Gateway\Exception\ReplyException;
use Shopwell\Core\Payment\Gateway\GatewayInterface;

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
