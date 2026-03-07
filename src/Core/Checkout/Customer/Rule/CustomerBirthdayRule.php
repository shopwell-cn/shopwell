<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Customer\Rule;

use Shopwell\Core\Checkout\CheckoutRuleScope;
use Shopwell\Core\Checkout\Customer\CustomerException;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Rule\Rule;
use Shopwell\Core\Framework\Rule\RuleComparison;
use Shopwell\Core\Framework\Rule\RuleConfig;
use Shopwell\Core\Framework\Rule\RuleConstraints;
use Shopwell\Core\Framework\Rule\RuleScope;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class CustomerBirthdayRule extends Rule
{
    final public const RULE_NAME = 'customerBirthday';

    /**
     * @internal
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected ?string $birthday = null
    ) {
        parent::__construct();
    }

    public function getConstraints(): array
    {
        $constraints = [
            'operator' => RuleConstraints::dateOperators(),
        ];

        if ($this->operator === self::OPERATOR_EMPTY) {
            return $constraints;
        }

        $constraints['birthday'] = RuleConstraints::date();

        return $constraints;
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CheckoutRuleScope) {
            return false;
        }

        if ($this->birthday === null && $this->operator !== self::OPERATOR_EMPTY) {
            throw CustomerException::unsupportedValue(\gettype($this->birthday), self::class);
        }

        if (!$customer = $scope->getSalesChannelContext()->getCustomer()) {
            return RuleComparison::isNegativeOperator($this->operator);
        }
        $customerBirthday = $customer->getBirthday();

        if ($customerBirthday instanceof \DateTimeImmutable) {
            $customerBirthday = \DateTime::createFromImmutable($customerBirthday);
        }

        if ($this->operator === self::OPERATOR_EMPTY) {
            return $customerBirthday === null;
        }

        if (
            !$customerBirthday instanceof \DateTime
            || !$this->birthday
            || \strtotime($this->birthday) === false
        ) {
            return RuleComparison::isNegativeOperator($this->operator);
        }

        $birthdayValue = new \DateTime($this->birthday);

        return RuleComparison::date($customerBirthday, $birthdayValue, $this->operator);
    }

    public function getConfig(): RuleConfig
    {
        return new RuleConfig()
            ->operatorSet(RuleConfig::OPERATOR_SET_NUMBER, true)
            ->dateField('birthday');
    }
}
