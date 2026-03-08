<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentSystem\Method\Entity;

use Shopwell\Core\Content\Media\MediaEntity;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Field;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\FieldType;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\ForeignKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\ManyToOne;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Translations;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\PluginEntity;
use Shopwell\Core\Framework\Struct\ArrayEntity;

#[Package('framework')]
#[Entity('payment_method', since: '6.0.0.0')]
class PaymentMethodEntity extends EntityStruct
{
    use EntityCustomFieldsTrait;

    #[PrimaryKey]
    #[Field(type: FieldType::UUID, api: true)]
    public string $id;

    #[ForeignKey(entity: 'plugin', api: true)]
    public ?string $pluginId = null;

    #[ForeignKey(entity: 'media', api: true)]
    public ?string $mediaId = null;

    #[ManyToOne(entity: 'plugin', api: true)]
    public ?PluginEntity $plugin = null;

    #[ManyToOne(entity: 'media', api: true)]
    public ?MediaEntity $media = null;

    #[Field(type: FieldType::BOOL, api: true)]
    public bool $active;

    #[Field(type: FieldType::INT, api: true)]
    public int $position;

    #[Field(type: FieldType::STRING, translated: true, api: true)]
    public ?string $name = null;

    /**
     * @var array<string, ArrayEntity>|null
     */
    #[Translations]
    public ?array $translations = null;
}
