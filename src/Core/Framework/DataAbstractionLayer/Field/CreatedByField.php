<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\DataAbstractionLayer\Field;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\FieldSerializer\CreatedByFieldSerializer;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\User\UserDefinition;

#[Package('framework')]
class CreatedByField extends FkField
{
    public function __construct(private readonly array $allowedWriteScopes = [Context::SYSTEM_SCOPE])
    {
        parent::__construct('created_by_id', 'createdById', UserDefinition::class);
    }

    public function getAllowedWriteScopes(): array
    {
        return $this->allowedWriteScopes;
    }

    protected function getSerializerClass(): string
    {
        return CreatedByFieldSerializer::class;
    }
}
