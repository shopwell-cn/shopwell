<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Blog;

use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Field;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\FieldType;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\Log\Package;

#[Package('discovery')]
class BlogEntity extends EntityStruct
{
    use EntityCustomFieldsTrait;

    #[PrimaryKey]
    #[Field(type: FieldType::UUID, api: true)]
    public string $id;
}
