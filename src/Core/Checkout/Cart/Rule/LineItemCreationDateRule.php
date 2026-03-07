<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Cart\Rule;

use Shopwell\Core\Checkout\Cart\CartException;
use Shopwell\Core\Checkout\Cart\LineItem\LineItem;
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
class LineItemCreationDateRule extends Rule
{
    final public const RULE_NAME = 'cartLineItemCreationDate';

    /**
     * @internal
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected ?string $lineItemCreationDate = null
    ) {
        parent::__construct();
    }

    public function getConstraints(): array
    {
        return [
            'lineItemCreationDate' => RuleConstraints::datetime(),
            'operator' => RuleConstraints::datetimeOperators(false),
        ];
    }

    public function match(RuleScope $scope): bool
    {
        if ($this->lineItemCreationDate === null) {
            return false;
        }

        try {
            $ruleValue = $this->buildDate($this->lineItemCreationDate);
        } catch (\Exception) {
            return false;
        }

        if ($scope instanceof LineItemScope) {
            return $this->matchesCreationDate($scope->getLineItem(), $ruleValue);
        }

        if (!$scope instanceof CartRuleScope) {
            return false;
        }

        foreach ($scope->getCart()->getLineItems()->filterGoodsFlat() as $lineItem) {
            if ($this->matchesCreationDate($lineItem, $ruleValue)) {
                return true;
            }
        }

        return false;
    }

    public function getConfig(): RuleConfig
    {
        return new RuleConfig()
            ->operatorSet(RuleConfig::OPERATOR_SET_NUMBER)
            ->dateTimeField('lineItemCreationDate');
    }

    /**
     * @throws CartException
     */
    private function matchesCreationDate(LineItem $lineItem, \DateTime $ruleValue): bool
    {
        try {
            /** @var string|null $itemCreatedString */
            $itemCreatedString = $lineItem->getPayloadValue('createdAt');

            if ($itemCreatedString === null) {
                return RuleComparison::isNegativeOperator($this->operator);
            }

            $itemCreated = $this->buildDate($itemCreatedString);
        } catch (\Exception) {
            return false;
        }

        return RuleComparison::datetime($itemCreated, $ruleValue, $this->operator);
    }

    /**
     * @throws \Exception
     */
    private function buildDate(string $dateString): \DateTime
    {
        $dateTime = new \DateTime($dateString);

        return $dateTime;
    }
}
