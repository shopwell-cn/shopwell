<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Promotion\Aggregate\PromotionDiscount;

use Shopwell\Core\Checkout\Promotion\Aggregate\PromotionDiscountPrice\PromotionDiscountPriceCollection;
use Shopwell\Core\Checkout\Promotion\PromotionEntity;
use Shopwell\Core\Content\Rule\RuleCollection;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopwell\Core\Framework\Log\Package;

#[Package('checkout')]
class PromotionDiscountEntity extends Entity
{
    use EntityIdTrait;

    final public const string SCOPE_CART = 'cart';
    final public const string SCOPE_DELIVERY = 'delivery';
    final public const string SCOPE_SET = 'set';
    final public const string SCOPE_SETGROUP = 'setgroup';

    final public const string TYPE_PERCENTAGE = 'percentage';
    final public const string TYPE_ABSOLUTE = 'absolute';
    final public const string TYPE_FIXED_UNIT = 'fixed_unit';
    final public const string TYPE_FIXED = 'fixed';

    protected string $promotionId;

    protected string $scope;

    protected string $type;

    protected float $value;

    protected ?PromotionEntity $promotion = null;

    protected ?RuleCollection $discountRules = null;

    protected bool $considerAdvancedRules;

    protected ?float $maxValue = null;

    protected ?PromotionDiscountPriceCollection $promotionDiscountPrices = null;

    protected ?string $sorterKey = null;

    protected ?string $applierKey = null;

    protected ?string $usageKey = null;

    protected ?string $pickerKey = null;

    public function getPromotionId(): string
    {
        return $this->promotionId;
    }

    public function setPromotionId(string $promotionId): void
    {
        $this->promotionId = $promotionId;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): void
    {
        $this->value = $value;
    }

    public function getPromotion(): ?PromotionEntity
    {
        return $this->promotion;
    }

    public function setPromotion(PromotionEntity $promotion): void
    {
        $this->promotion = $promotion;
    }

    public function getDiscountRules(): ?RuleCollection
    {
        return $this->discountRules;
    }

    public function setDiscountRules(RuleCollection $discountRules): void
    {
        $this->discountRules = $discountRules;
    }

    public function getPromotionDiscountPrices(): ?PromotionDiscountPriceCollection
    {
        return $this->promotionDiscountPrices;
    }

    public function setPromotionDiscountPrices(PromotionDiscountPriceCollection $promotionDiscountPrices): void
    {
        $this->promotionDiscountPrices = $promotionDiscountPrices;
    }

    public function isConsiderAdvancedRules(): bool
    {
        return $this->considerAdvancedRules;
    }

    public function setConsiderAdvancedRules(bool $considerAdvancedRules): void
    {
        $this->considerAdvancedRules = $considerAdvancedRules;
    }

    public function getMaxValue(): ?float
    {
        return $this->maxValue;
    }

    public function setMaxValue(?float $maxValue): void
    {
        $this->maxValue = $maxValue;
    }

    public function isScopeSetGroup(): bool
    {
        $prefix = PromotionDiscountEntity::SCOPE_SETGROUP . '-';

        return mb_strpos($this->scope, $prefix) === 0;
    }

    public function getSetGroupId(): string
    {
        if (!$this->isScopeSetGroup()) {
            return '';
        }
        $prefix = PromotionDiscountEntity::SCOPE_SETGROUP . '-';

        return str_replace($prefix, '', $this->scope);
    }

    public function getSorterKey(): ?string
    {
        return $this->sorterKey;
    }

    public function setSorterKey(?string $sorterKey): void
    {
        $this->sorterKey = $sorterKey;
    }

    public function getApplierKey(): ?string
    {
        return $this->applierKey;
    }

    public function setApplierKey(?string $applierKey): void
    {
        $this->applierKey = $applierKey;
    }

    public function getUsageKey(): ?string
    {
        return $this->usageKey;
    }

    public function setUsageKey(?string $usageKey): void
    {
        $this->usageKey = $usageKey;
    }

    public function getPickerKey(): string
    {
        return (string) $this->pickerKey;
    }

    public function setPickerKey(string $pickerKey): void
    {
        $this->pickerKey = $pickerKey;
    }
}
