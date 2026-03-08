<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Rule\Container;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Rule\RuleScope;

#[Package('fundamentals@after-sales')]
class OrRule extends Container
{
    final public const string RULE_NAME = 'orContainer';

    public function match(RuleScope $scope): bool
    {
        return array_any($this->rules, fn ($rule) => $rule->match($scope));
    }
}
