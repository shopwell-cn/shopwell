<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
class RuleAreas extends Flag
{
    final public const string PRODUCT_AREA = 'product';
    final public const string PAYMENT_AREA = 'payment';
    final public const string SHIPPING_AREA = 'shipping';
    final public const string PROMOTION_AREA = 'promotion';
    final public const string FLOW_AREA = 'flow';
    final public const string FLOW_CONDITION_AREA = 'flow-condition';

    /**
     * @var string[]
     */
    private readonly array $areas;

    public function __construct(string ...$areas)
    {
        $this->areas = $areas;
    }

    public function parse(): \Generator
    {
        yield 'rule_areas' => true;
    }

    /**
     * @return string[]
     */
    public function getAreas(): array
    {
        return $this->areas;
    }
}
