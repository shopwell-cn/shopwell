<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict;

use Shopwell\Core\Framework\DataAbstractionLayer\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopwell\Core\Framework\Log\Package;

#[Package('data-services')]
class DataDictGroupEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    public string $code;

    public int $position;
}
