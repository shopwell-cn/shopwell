<?php

declare(strict_types=1);

namespace Shopwell\Core\Content\Cookie;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class CookieException extends HttpException
{
    final public const string NOT_ALLOWED_PROPERTY_ASSIGNMENT = 'CONTENT__COOKIE_NOT_ALLOWED_PROPERTY_ASSIGNMENT';
    final public const string HASH_GENERATION_FAILED = 'CONTENT__COOKIE_HASH_GENERATION_FAILED';

    public static function notAllowedPropertyAssignment(string $propertyToBeAssigned, string $alreadyAssignedProperty): self
    {
        return new self(
            Response::HTTP_FORBIDDEN,
            self::NOT_ALLOWED_PROPERTY_ASSIGNMENT,
            'Property "{{ propertyToBeAssigned }}" cannot be set, as "{{ alreadyAssignedProperty }}" is already set.',
            ['propertyToBeAssigned' => $propertyToBeAssigned, 'alreadyAssignedProperty' => $alreadyAssignedProperty],
        );
    }

    public static function hashGenerationFailed(string $reason): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::HASH_GENERATION_FAILED,
            'Failed to generate cookie configuration hash: {{ reason }}',
            ['reason' => $reason],
        );
    }
}
