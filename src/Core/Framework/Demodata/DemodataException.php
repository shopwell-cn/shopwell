<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Demodata;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('framework')]
class DemodataException extends HttpException
{
    final public const WRONG_EXECUTION_ORDER = 'FRAMEWORK__WRONG_EXECUTION_ORDER';
    final public const NO_GENERATOR_FOUND = 'FRAMEWORK__NO_GENERATOR_FOUND';

    public static function wrongExecutionOrder(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::WRONG_EXECUTION_ORDER,
            'This demo data command should be executed after the original demo data was executed at least one time',
            []
        );
    }

    public static function noGeneratorFound(string $entity): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::NO_GENERATOR_FOUND,
            'Could not generate demodata for "{{ entity }}" because no generator is registered.',
            ['entity' => $entity]
        );
    }
}
