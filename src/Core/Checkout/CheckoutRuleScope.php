<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Rule\RuleScope;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class CheckoutRuleScope extends RuleScope
{
    public function __construct(
        protected SalesChannelContext $context
    ) {
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->context;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }
}
