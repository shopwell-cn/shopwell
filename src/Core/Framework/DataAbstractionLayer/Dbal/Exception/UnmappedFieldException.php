<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\DataAbstractionLayer\Dbal\Exception;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\ShopwellHttpException;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class UnmappedFieldException extends ShopwellHttpException
{
    public function __construct(
        string $field,
        EntityDefinition $definition
    ) {
        $fieldParts = explode('.', $field);
        $name = array_pop($fieldParts);

        parent::__construct(
            'Field "{{ field }}" in entity "{{ entity }}" was not found.',
            ['field' => $name, 'entity' => $definition->getEntityName()]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__UNMAPPED_FIELD';
    }
}
