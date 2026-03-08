<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Promotion;

use Shopwell\Core\Checkout\Cart\LineItem\LineItem;
use Shopwell\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountEntity;
use Shopwell\Core\Checkout\Promotion\Exception\InvalidCodePatternException;
use Shopwell\Core\Checkout\Promotion\Exception\PatternNotComplexEnoughException;
use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class PromotionException extends HttpException
{
    public const string PROMOTION_CODE_ALREADY_REDEEMED = 'CHECKOUT__CODE_ALREADY_REDEEMED';
    public const string DISCOUNT_CALCULATOR_NOT_FOUND = 'CHECKOUT__PROMOTION_DISCOUNT_CALCULATOR_NOT_FOUND';
    public const string INVALID_CODE_PATTERN = 'CHECKOUT__INVALID_CODE_PATTERN';
    public const string INVALID_DISCOUNT_SCOPE_DEFINITION = 'CHECKOUT__PROMOTION_INVALID_DISCOUNT_SCOPE_DEFINITION';
    public const string PATTERN_NOT_COMPLEX_ENOUGH = 'PROMOTION__INDIVIDUAL_CODES_PATTERN_INSUFFICIENTLY_COMPLEX';
    public const string PATTERN_ALREADY_IN_USE = 'PROMOTION__INDIVIDUAL_CODES_PATTERN_ALREADY_IN_USE';
    public const string PROMOTION_NOT_FOUND = 'CHECKOUT__PROMOTION__NOT_FOUND';
    public const string PROMOTION_DISCOUNT_NOT_FOUND = 'CHECKOUT__PROMOTION_DISCOUNT_NOT_FOUND';
    public const string PROMOTION_CODE_NOT_FOUND = 'CHECKOUT__PROMOTION_CODE_NOT_FOUND';
    public const string PROMOTION_INVALID_PRICE_DEFINITION = 'CHECKOUT__INVALID_DISCOUNT_PRICE_DEFINITION';
    public const string CHECKOUT_UNKNOWN_PROMOTION_DISCOUNT_TYPE = 'CHECKOUT__UNKNOWN_PROMOTION_DISCOUNT_TYPE';
    public const string PROMOTION_SET_GROUP_NOT_FOUND = 'CHECKOUT__PROMOTION_SETGROUP_NOT_FOUND';
    public const string MISSING_REQUEST_PARAMETER_CODE = 'CHECKOUT__MISSING_REQUEST_PARAMETER';
    public const string PRICE_NOT_FOUND_FOR_ITEM = 'CHECKOUT__PRICE_NOT_FOUND_FOR_ITEM';
    public const string FILTER_SORTER_NOT_FOUND = 'CHECKOUT__FILTER_SORTER_NOT_FOUND';
    public const string FILTER_PICKER_NOT_FOUND = 'CHECKOUT__FILTER_PICKER_NOT_FOUND';
    public const string PROMOTION_USAGE_LOCKED = 'CHECKOUT__PROMOTION_USAGE_LOCKED';
    public const string PROMOTION_USED_DELETE_RESTRICTION = 'CHECKOUT__PROMOTION_USED_DELETE_RESTRICTION';

    public static function codeAlreadyRedeemed(string $code): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::PROMOTION_CODE_ALREADY_REDEEMED,
            'Promo code "{{ code }}" has already been marked as redeemed!',
            ['code' => $code]
        );
    }

    public static function discountCalculatorNotFound(string $type): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::DISCOUNT_CALCULATOR_NOT_FOUND,
            'Promotion Discount Calculator "{{ type }}" has not been found!',
            ['type' => $type]
        );
    }

    public static function invalidCodePattern(string $codePattern): self
    {
        return new InvalidCodePatternException(
            Response::HTTP_BAD_REQUEST,
            self::INVALID_CODE_PATTERN,
            'Invalid code pattern "{{ codePattern }}".',
            ['codePattern' => $codePattern]
        );
    }

    public static function invalidScopeDefinition(string $scope): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::INVALID_DISCOUNT_SCOPE_DEFINITION,
            'Invalid discount calculator scope definition "{{ label }}"',
            ['label' => $scope]
        );
    }

    public static function patternNotComplexEnough(): self
    {
        return new PatternNotComplexEnoughException(
            Response::HTTP_BAD_REQUEST,
            self::PATTERN_NOT_COMPLEX_ENOUGH,
            'The amount of possible codes is too low for the current pattern. Make sure your pattern is sufficiently complex.'
        );
    }

    public static function patternAlreadyInUse(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::PATTERN_ALREADY_IN_USE,
            'Code pattern already exists in another promotion. Please provide a different pattern.'
        );
    }

    /**
     * @param string[] $ids
     */
    public static function promotionsNotFound(array $ids): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::PROMOTION_NOT_FOUND,
            'These promotions "{{ ids }}" are not found',
            ['ids' => implode(', ', $ids)]
        );
    }

    /**
     * @param string[] $ids
     */
    public static function discountsNotFound(array $ids): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::PROMOTION_DISCOUNT_NOT_FOUND,
            'These promotion discounts "{{ ids }}" are not found',
            ['ids' => implode(', ', $ids)]
        );
    }

    public static function promotionCodeNotFound(string $code): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::PROMOTION_CODE_NOT_FOUND,
            'Promotion code "{{ code }}" has not been found!',
            ['code' => $code]
        );
    }

    /**
     * @param list<string> $codes
     */
    public static function promotionCodesNotFound(array $codes): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::PROMOTION_CODE_NOT_FOUND,
            'None of the promotion codes "{{ code }}" have not been found!',
            ['code' => \implode(', ', $codes)]
        );
    }

    public static function invalidPriceDefinition(string $label, ?string $code): self
    {
        if ($code === null) {
            $messages = [
                'Invalid discount price definition for automated promotion "{{ label }}"',
                ['label' => $label],
            ];
        } else {
            $messages = [
                'Invalid discount price definition for promotion line item with code "{{ code }}"',
                ['code' => $code],
            ];
        }

        return new self(
            Response::HTTP_BAD_REQUEST,
            self::PROMOTION_INVALID_PRICE_DEFINITION,
            ...$messages,
        );
    }

    public static function unknownPromotionDiscountType(PromotionDiscountEntity $discount): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::CHECKOUT_UNKNOWN_PROMOTION_DISCOUNT_TYPE,
            'Unknown promotion discount type detected: {{ type }}',
            ['type' => $discount->getType()]
        );
    }

    public static function promotionSetGroupNotFound(string $groupId): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::PROMOTION_SET_GROUP_NOT_FOUND,
            'Promotion SetGroup "{{ id }}" has not been found!',
            ['id' => $groupId],
        );
    }

    public static function missingRequestParameter(string $name): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::MISSING_REQUEST_PARAMETER_CODE,
            'Parameter "{{ parameterName }}" is missing.',
            ['parameterName' => $name]
        );
    }

    public static function priceNotFound(LineItem $lineItem): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::PRICE_NOT_FOUND_FOR_ITEM,
            'No calculated price found for item {{ id }}',
            ['id' => $lineItem->getId()]
        );
    }

    public static function filterSorterNotFound(string $key): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::FILTER_SORTER_NOT_FOUND,
            'Sorter "{{ key }}" has not been found!',
            ['key' => $key]
        );
    }

    public static function filterPickerNotFoundException(string $key): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::FILTER_PICKER_NOT_FOUND,
            'Picker "{{ key }}" has not been found!',
            ['key' => $key]
        );
    }

    public static function promotionUsageLocked(string $promotionCodeOrId): self
    {
        return new self(
            Response::HTTP_CONFLICT,
            self::PROMOTION_USAGE_LOCKED,
            'Promotion {{ promotion }} is locked due to concurrent write operation. Please try again later.',
            ['promotion' => $promotionCodeOrId]
        );
    }

    public static function promotionUsedDeleteRestriction(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::PROMOTION_USED_DELETE_RESTRICTION,
            'Promotions cannot be deleted once they have been used in an order.',
            [],
        );
    }
}
