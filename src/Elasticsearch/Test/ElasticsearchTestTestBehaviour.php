<?php declare(strict_types=1);

namespace Shopwell\Elasticsearch\Test;

use Doctrine\DBAL\Connection;
use OpenSearch\Client;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use Shopwell\Core\DevOps\Environment\EnvironmentHelper;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\EntityAggregator;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\EntitySearcher;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Elasticsearch\Framework\Command\ElasticsearchIndexingCommand;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\AbstractElasticsearchAggregationHydrator;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\AbstractElasticsearchSearchHydrator;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\CriteriaParser;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\ElasticsearchEntityAggregator;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\ElasticsearchEntitySearcher;
use Shopwell\Elasticsearch\Framework\ElasticsearchHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[Package('framework')]
trait ElasticsearchTestTestBehaviour
{
    #[Before]
    public function enableElasticsearch(): void
    {
        $this->getDiContainer()
            ->get(ElasticsearchHelper::class)
            ->setEnabled(true);
    }

    #[After]
    public function disableElasticsearch(): void
    {
        $this->getDiContainer()
            ->get(ElasticsearchHelper::class)
            ->setEnabled(false);
    }

    public function indexElasticSearch(): void
    {
        $this->getDiContainer()
            ->get(ElasticsearchIndexingCommand::class)
            ->run(new ArrayInput([]), new NullOutput());

        $this->runWorker();

        $this->refreshIndex();
    }

    public function refreshIndex(): void
    {
        $this->getDiContainer()->get(Client::class)
            ->indices()
            ->refresh(['index' => '*']);
    }

    protected function createEntityAggregator(): ElasticsearchEntityAggregator
    {
        $decorated = $this->createMock(EntityAggregator::class);

        $decorated
            ->expects(static::never())
            ->method('aggregate');

        return new ElasticsearchEntityAggregator(
            $this->getDiContainer()->get(ElasticsearchHelper::class),
            $this->getDiContainer()->get(Client::class),
            $decorated,
            $this->getDiContainer()->get(AbstractElasticsearchAggregationHydrator::class),
            $this->getDiContainer()->get('event_dispatcher'),
            '5s',
            'dfs_query_then_fetch'
        );
    }

    protected function createEntitySearcher(): ElasticsearchEntitySearcher
    {
        $decorated = $this->createMock(EntitySearcher::class);

        $decorated
            ->expects(static::never())
            ->method('search');

        return new ElasticsearchEntitySearcher(
            $this->getDiContainer()->get(Client::class),
            $decorated,
            $this->getDiContainer()->get(ElasticsearchHelper::class),
            $this->getDiContainer()->get(CriteriaParser::class),
            $this->getDiContainer()->get(AbstractElasticsearchSearchHydrator::class),
            $this->getDiContainer()->get('event_dispatcher'),
            '5s',
            'dfs_query_then_fetch'
        );
    }

    abstract protected function getDiContainer(): ContainerInterface;

    abstract protected function runWorker(): void;

    protected function clearElasticsearch(): void
    {
        $c = $this->getDiContainer();

        $client = $c->get(Client::class);

        $indices = $client->indices()->get(['index' => EnvironmentHelper::getVariable('SHOPWELL_ES_INDEX_PREFIX') . '*']);

        foreach ($indices as $name => $index) {
            $client->indices()->delete(['index' => $name]);
        }

        $connection = $c->get(Connection::class);
        $connection->executeStatement('DELETE FROM elasticsearch_index_task');
    }
}
