<?php declare(strict_types=1);

namespace Shopwell\Core\System\Language;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('fundamentals@discovery')]
class LanguageException extends HttpException
{
    public const string VALUE_NOT_SUPPORTED = 'LANGUAGE__RULE_VALUE_NOT_SUPPORTED';

    public static function unsupportedValue(string $type, string $class): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::VALUE_NOT_SUPPORTED,
            'Unsupported value of type {{ type }} in {{ class }}',
            ['type' => $type, 'class' => $class]
        );
    }
}
