<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict\Aggregate\DataDictItem;

use Shopwell\Core\Framework\DataAbstractionLayer\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\DataDict\DataDictGroupEntity;

#[Package('data-services')]
class DataDictItemEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    public string $optionValue;

    public int $active;

    public int $position;

    public string $groupId;

    public string $path;

    public ?DataDictGroupEntity $group = null;
}
