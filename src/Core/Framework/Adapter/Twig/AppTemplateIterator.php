<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Adapter\Twig;

use Shopwell\Core\Framework\Adapter\Database\MySQLFactory;
use Shopwell\Core\Framework\App\Template\TemplateCollection;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\TermsAggregation;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\TermsResult;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\Log\Package;

/**
 * @implements \IteratorAggregate<int, string>
 */
#[Package('framework')]
class AppTemplateIterator implements \IteratorAggregate
{
    /**
     * @internal
     *
     * @param EntityRepository<TemplateCollection> $templateRepository
     */
    public function __construct(
        private readonly \IteratorAggregate $templateIterator,
        private readonly EntityRepository $templateRepository
    ) {
    }

    public function getIterator(): \Traversable
    {
        yield from $this->templateIterator;

        yield from $this->getDatabaseTemplatePaths();
    }

    /**
     * @return array<string>
     */
    private function getDatabaseTemplatePaths(): array
    {
        if (MySQLFactory::hasNoDatabaseAvailable()) {
            return [];
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addAggregation(
            new TermsAggregation('path-names', 'path')
        );

        /** @var TermsResult $pathNames */
        $pathNames = $this->templateRepository->aggregate(
            $criteria,
            Context::createDefaultContext()
        )->get('path-names');

        return $pathNames->getKeys();
    }
}
