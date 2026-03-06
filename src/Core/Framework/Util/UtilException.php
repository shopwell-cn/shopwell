<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Util;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class UtilException extends HttpException
{
    public const INVALID_JSON = 'UTIL_INVALID_JSON';
    public const COULD_NOT_HASH_FILE = 'UTIL__COULD_NOT_HASH_FILE';

    public static function invalidJson(\JsonException $e): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::INVALID_JSON,
            'JSON is invalid',
            [],
            $e
        );
    }
    public static function couldNotHashFile(string $file): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::COULD_NOT_HASH_FILE,
            'Could not generate hash for  "{{ file }}"',
            ['file' => $file]
        );
    }
}
