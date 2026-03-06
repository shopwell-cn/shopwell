<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Customer\Rule;

use Shopwell\Core\Checkout\CheckoutRuleScope;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Rule\Container\DaysSinceRule;
use Shopwell\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class DaysSinceFirstLoginRule extends DaysSinceRule
{
    final public const RULE_NAME = 'customerDaysSinceFirstLogin';

    protected function getDate(RuleScope $scope): ?\DateTimeInterface
    {
        return $scope->getSalesChannelContext()->getCustomer()?->getFirstLogin();
    }

    protected function supportsScope(RuleScope $scope): bool
    {
        return $scope instanceof CheckoutRuleScope;
    }
}
