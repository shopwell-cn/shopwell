<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Collection;

/**
 * @extends Collection<DocumentIdStruct>
 */
#[Package('after-sales')]
class DocumentIdCollection extends Collection
{
    public function getApiAlias(): string
    {
        return 'document_id_collection';
    }

    protected function getExpectedClass(): ?string
    {
        return DocumentIdStruct::class;
    }
}
