<?php declare(strict_types=1);

namespace Shopwell\Elasticsearch\Product;

use Shopwell\Core\Content\Product\DataAbstractionLayer\SearchKeywordUpdater;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Elasticsearch\Framework\ElasticsearchHelper;

#[Package('framework')]
class SearchKeywordReplacement extends SearchKeywordUpdater
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SearchKeywordUpdater $decorated,
        private readonly ElasticsearchHelper $helper
    ) {
    }

    /**
     * @param array<string> $ids
     */
    public function update(array $ids, Context $context): void
    {
        if ($this->helper->allowIndexing()) {
            return;
        }

        $this->decorated->update($ids, $context);
    }

    public function reset(): void
    {
        $this->decorated->reset();
    }
}
