<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Cart\Rule;

use Shopwell\Core\Checkout\Cart\CartException;
use Shopwell\Core\Checkout\Cart\LineItem\LineItem;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Rule\CustomFieldRule;
use Shopwell\Core\Framework\Rule\Rule;
use Shopwell\Core\Framework\Rule\RuleComparison;
use Shopwell\Core\Framework\Rule\RuleScope;
use Shopwell\Core\Framework\Util\ArrayComparator;
use Shopwell\Core\Framework\Util\FloatComparator;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Constraint;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class LineItemCustomFieldRule extends Rule
{
    final public const RULE_NAME = 'cartLineItemCustomField';

    /**
     * @var array<string|int|bool|float>|string|int|float|bool|null
     */
    protected array|string|int|float|bool|null $renderedFieldValue = null;

    protected ?string $selectedField = null;

    protected ?string $selectedFieldSet = null;

    /**
     * @param array<string, mixed> $renderedField
     *
     * @internal
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected array $renderedField = []
    ) {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if ($scope instanceof LineItemScope) {
            return $this->isCustomFieldValid($scope->getLineItem(), $scope->getSalesChannelContext());
        }

        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        foreach ($scope->getCart()->getLineItems()->filterGoodsFlat() as $lineItem) {
            if ($this->isCustomFieldValid($lineItem, $scope->getSalesChannelContext())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array|Constraint[][]
     */
    public function getConstraints(): array
    {
        return CustomFieldRule::getConstraints($this->renderedField);
    }

    private function isCustomFieldValid(LineItem $lineItem, SalesChannelContext $context): bool
    {
        $customFields = $lineItem->getPayloadValue('customFields');
        if ($customFields === null) {
            return RuleComparison::isNegativeOperator($this->operator);
        }

        $actual = CustomFieldRule::getValue($customFields, $this->renderedField, $context);
        $expected = CustomFieldRule::getExpectedValue($this->renderedFieldValue, $this->renderedField);

        if ($actual === null) {
            if ($this->operator === self::OPERATOR_NEQ) {
                return $actual !== $expected;
            }

            return false;
        }

        if (CustomFieldRule::isFloat($this->renderedField)) {
            return FloatComparator::compare((float) $actual, (float) $expected, $this->operator);
        }

        if (CustomFieldRule::isArray($this->renderedField)) {
            return ArrayComparator::compare((array) $actual, (array) $expected, $this->operator);
        }

        return match ($this->operator) {
            self::OPERATOR_NEQ => $actual !== $expected,
            self::OPERATOR_GTE => $actual >= $expected,
            self::OPERATOR_LTE => $actual <= $expected,
            self::OPERATOR_EQ => $actual === $expected,
            self::OPERATOR_GT => $actual > $expected,
            self::OPERATOR_LT => $actual < $expected,
            default => throw CartException::unsupportedOperator($this->operator, self::class),
        };
    }
}
