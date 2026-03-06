<?php declare(strict_types=1);

namespace Shopwell\Administration\Notification;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityProtection\EntityProtectionCollection;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityProtection\ReadProtection;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityProtection\WriteProtection;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\ListField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopwell\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopwell\Core\Framework\Feature;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\Integration\IntegrationDefinition;
use Shopwell\Core\System\User\UserDefinition;

/**
 * @deprecated tag:v6.8.0 - Will be removed in 6.8.0. Use Shopwell\Core\Framework\Notification\NotificationDefinition instead
 */
#[Package('framework')]
class NotificationDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'notification';

    public function getEntityName(): string
    {
        Feature::triggerDeprecationOrThrow('v6.8.0.0', Feature::deprecatedClassMessage(self::class, 'v6.8.0.0', \Shopwell\Core\Framework\Notification\NotificationEntity::class));

        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        Feature::triggerDeprecationOrThrow('v6.8.0.0', Feature::deprecatedClassMessage(self::class, 'v6.8.0.0', \Shopwell\Core\Framework\Notification\NotificationEntity::class));

        return NotificationCollection::class;
    }

    public function getEntityClass(): string
    {
        Feature::triggerDeprecationOrThrow('v6.8.0.0', Feature::deprecatedClassMessage(self::class, 'v6.8.0.0', \Shopwell\Core\Framework\Notification\NotificationEntity::class));

        return NotificationEntity::class;
    }

    public function getDefaults(): array
    {
        Feature::triggerDeprecationOrThrow('v6.8.0.0', Feature::deprecatedClassMessage(self::class, 'v6.8.0.0', \Shopwell\Core\Framework\Notification\NotificationEntity::class));

        return [
            'requiredPrivileges' => [],
            'adminOnly' => false,
        ];
    }

    public function since(): ?string
    {
        Feature::triggerDeprecationOrThrow('v6.8.0.0', Feature::deprecatedClassMessage(self::class, 'v6.8.0.0', \Shopwell\Core\Framework\Notification\NotificationEntity::class));

        return '6.4.7.0';
    }

    protected function defineProtections(): EntityProtectionCollection
    {
        Feature::triggerDeprecationOrThrow('v6.8.0.0', Feature::deprecatedClassMessage(self::class, 'v6.8.0.0', \Shopwell\Core\Framework\Notification\NotificationEntity::class));

        return new EntityProtectionCollection([
            new ReadProtection(Context::SYSTEM_SCOPE),
            new WriteProtection(Context::SYSTEM_SCOPE),
        ]);
    }

    protected function defineFields(): FieldCollection
    {
        Feature::triggerDeprecationOrThrow('v6.8.0.0', Feature::deprecatedClassMessage(self::class, 'v6.8.0.0', \Shopwell\Core\Framework\Notification\NotificationEntity::class));

        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('status', 'status'))->addFlags(new Required()),
            (new LongTextField('message', 'message'))->addFlags(new Required()),
            new BoolField('admin_only', 'adminOnly'),
            new ListField('required_privileges', 'requiredPrivileges'),

            new FkField('created_by_integration_id', 'createdByIntegrationId', IntegrationDefinition::class),
            new FkField('created_by_user_id', 'createdByUserId', UserDefinition::class),

            new ManyToOneAssociationField('createdByIntegration', 'created_by_integration_id', IntegrationDefinition::class, 'id', false),
            new ManyToOneAssociationField('createdByUser', 'created_by_user_id', UserDefinition::class, 'id', false),
        ]);
    }
}
