<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Rule;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Script\Exception\ScriptExecutionFailedException;
use Shopwell\Core\Framework\Script\ScriptException;
use Symfony\Component\HttpFoundation\Response;

#[Package('fundamentals@after-sales')]
class RuleException extends HttpException
{
    final public const string RULE_OPERATOR_NOT_SUPPORTED = 'FRAMEWORK__RULE_OPERATOR_NOT_SUPPORTED';
    public const string VALUE_NOT_SUPPORTED = 'CONTENT__RULE_VALUE_NOT_SUPPORTED';
    public const string MULTIPLE_NOT_RULES = 'CONTENT__TOO_MANY_NOT_RULES';
    public const string INVALID_DATE_RANGE_USAGE = 'FRAMEWORK__INVALID_DATE_RANGE_USAGE';

    public static function scriptExecutionFailed(string $hook, string $scriptName, \Throwable $previous): ScriptException
    {
        // use own exception class so it can be caught properly
        return new ScriptExecutionFailedException($hook, $scriptName, $previous);
    }

    public static function unsupportedOperator(string $operator, string $class): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::RULE_OPERATOR_NOT_SUPPORTED,
            'Unsupported operator {{ operator }} in {{ class }}',
            ['operator' => $operator, 'class' => $class]
        );
    }

    public static function unsupportedValue(string $type, string $class): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::VALUE_NOT_SUPPORTED,
            'Unsupported value of type {{ type }} in {{ class }}',
            ['type' => $type, 'class' => $class]
        );
    }

    public static function onlyOneNotRule(): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::MULTIPLE_NOT_RULES,
            'NOT rule can only hold one rule'
        );
    }

    public static function invalidDateRangeUsage(string $reason): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::INVALID_DATE_RANGE_USAGE,
            'Invalid date range usage: {{ reason }}',
            ['reason' => $reason]
        );
    }
}
