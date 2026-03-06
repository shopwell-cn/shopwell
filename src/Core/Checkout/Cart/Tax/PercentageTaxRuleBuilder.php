<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Cart\Tax;

use Shopwell\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopwell\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopwell\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopwell\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopwell\Core\Framework\Log\Package;

#[Package('checkout')]
class PercentageTaxRuleBuilder
{
    public function buildRules(CalculatedPrice $price): TaxRuleCollection
    {
        return $this->buildCollectionRules($price->getCalculatedTaxes(), $price->getTotalPrice());
    }

    public function buildCollectionRules(CalculatedTaxCollection $taxes, float $totalPrice): TaxRuleCollection
    {
        $rules = new TaxRuleCollection([]);

        foreach ($taxes as $tax) {
            $rules->add(
                new TaxRule(
                    $tax->getTaxRate(),
                    $totalPrice !== 0.0 ? $tax->getPrice() / $totalPrice * 100 : 0
                )
            );
        }

        return $rules;
    }
}
