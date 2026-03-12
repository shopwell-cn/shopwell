<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Rule;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;
use Symfony\Component\Validator\Constraint;

#[Package('fundamentals@after-sales')]
abstract class Rule extends Struct
{
    public const RULE_NAME = null;

    public const string OPERATOR_GTE = '>=';

    public const string OPERATOR_LTE = '<=';

    public const string OPERATOR_GT = '>';

    public const string OPERATOR_LT = '<';

    public const string OPERATOR_EQ = '=';

    public const string OPERATOR_NEQ = '!=';

    public const string OPERATOR_EMPTY = 'empty';

    /**
     * Factor to convert from m^3 to mm^3.
     * The product volume is calculated in cubic millimeters, but the rule value is stored in cubic meters.
     */
    public const int|float VOLUME_FACTOR = 1000 * 1000 * 1000;

    protected string $_name;

    protected string $operator;

    public function __construct()
    {
        $this->_name = $this->getName();
    }

    /**
     * Returns the api name for this rule. The name has to be unique in the system.
     */
    public function getName(): string
    {
        $ruleName = static::RULE_NAME;

        if ($ruleName === null) {
            throw new \Error('Implement own getName or add RULE_NAME constant');
        }

        return $ruleName;
    }

    /**
     * Validate the current rule and returns the matching of the rule
     */
    abstract public function match(RuleScope $scope): bool;

    /**
     * Gets the constraints of the rule
     * Format:
     *  [
     *   'propertyName' => [new Constraint(), new OtherConstraint()],
     *   'propertyName2' => [new Constraint(), new OtherConstraint()],
     *  ]
     *
     * @return array<string, array<Constraint>>
     */
    abstract public function getConstraints(): array;

    /**
     * Get the config which contains operators and fields to be rendered in the admin.
     */
    public function getConfig(): ?RuleConfig
    {
        return null;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        unset($data['extensions'], $data['_class']);
        $data['_name'] = $this->getName();

        // filter out null values to avoid constraint violations with empty operator
        return array_filter($data, static function ($value) {
            return $value !== null;
        });
    }

    public function getApiAlias(): string
    {
        return 'rule_' . $this->getName();
    }
}
