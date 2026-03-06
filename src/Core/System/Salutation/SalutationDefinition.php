<?php declare(strict_types=1);

namespace Shopwell\Core\System\Salutation;

use Shopwell\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressDefinition;
use Shopwell\Core\Checkout\Customer\CustomerDefinition;
use Shopwell\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressDefinition;
use Shopwell\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerDefinition;
use Shopwell\Core\Content\Newsletter\Aggregate\NewsletterRecipient\NewsletterRecipientDefinition;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopwell\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\Salutation\Aggregate\SalutationTranslation\SalutationTranslationDefinition;

#[Package('checkout')]
class SalutationDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'salutation';

    final public const NOT_SPECIFIED = 'not_specified';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return SalutationCollection::class;
    }

    public function getEntityClass(): string
    {
        return SalutationEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required())->setDescription('Unique identity of salutation.'),
            (new StringField('salutation_key', 'salutationKey'))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING))->setDescription('Technical name given to salutation. For example: mr'),
            (new TranslatedField('displayName'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('letterName'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),

            (new TranslationsAssociationField(SalutationTranslationDefinition::class, 'salutation_id'))->addFlags(new Required()),

            // Reverse Associations, not available in sales-channel-api
            (new OneToManyAssociationField('customers', CustomerDefinition::class, 'salutation_id', 'id'))->addFlags(new SetNullOnDelete()),
            (new OneToManyAssociationField('customerAddresses', CustomerAddressDefinition::class, 'salutation_id', 'id'))->addFlags(new SetNullOnDelete()),
            (new OneToManyAssociationField('orderCustomers', OrderCustomerDefinition::class, 'salutation_id', 'id'))->addFlags(new SetNullOnDelete()),
            (new OneToManyAssociationField('orderAddresses', OrderAddressDefinition::class, 'salutation_id', 'id'))->addFlags(new SetNullOnDelete()),
            (new OneToManyAssociationField('newsletterRecipients', NewsletterRecipientDefinition::class, 'salutation_id', 'id'))->addFlags(new SetNullOnDelete()),
        ]);
    }
}
