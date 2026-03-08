<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict\Aggregate\DataDictItemTranslation;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Shopwell\Core\Framework\Log\Package;

#[Package('data-services')]
class DataDictItemTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    public ?string $name;

    public ?string $description;
}
