<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\Aggregate\DocumentType;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<DocumentTypeEntity>
 */
#[Package('after-sales')]
class DocumentTypeCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'document_type_collection';
    }

    protected function getExpectedClass(): string
    {
        return DocumentTypeEntity::class;
    }
}
