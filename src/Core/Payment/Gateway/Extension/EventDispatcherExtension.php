<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway\Extension;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Payment\Gateway\Event\ExecuteEvent;
use Shopwell\Core\Payment\Gateway\GatewayEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Package('payment-system')]
class EventDispatcherExtension implements ExtensionInterface
{
    public function __construct(
        protected readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function onPreExecute(Context $context): void
    {
        $this->dispatcher->dispatch(new ExecuteEvent($context), GatewayEvents::GATEWAY_PRE_EXECUTE);
    }

    public function onExecute(Context $context): void
    {
        $this->dispatcher->dispatch(new ExecuteEvent($context), GatewayEvents::GATEWAY_EXECUTE);
    }

    public function onPostExecute(Context $context): void
    {
        $this->dispatcher->dispatch(new ExecuteEvent($context), GatewayEvents::GATEWAY_POST_EXECUTE);
    }
}
