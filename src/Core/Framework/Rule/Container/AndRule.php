<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Rule\Container;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Rule\RuleScope;

/**
 * AndRule returns true, if all child-rules are true
 */
#[Package('fundamentals@after-sales')]
class AndRule extends Container
{
    final public const string RULE_NAME = 'andContainer';

    public function match(RuleScope $scope): bool
    {
        return array_all($this->rules, fn ($rule) => $rule->match($scope));
    }
}
