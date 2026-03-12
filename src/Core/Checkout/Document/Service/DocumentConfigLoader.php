<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\Service;

use Shopwell\Core\Checkout\Document\Aggregate\DocumentBaseConfig\DocumentBaseConfigCollection;
use Shopwell\Core\Checkout\Document\Aggregate\DocumentBaseConfig\DocumentBaseConfigEntity;
use Shopwell\Core\Checkout\Document\DocumentConfiguration;
use Shopwell\Core\Checkout\Document\DocumentConfigurationFactory;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Uuid\Uuid;
use Shopwell\Core\System\Country\CountryCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ResetInterface;

#[Package('after-sales')]
final class DocumentConfigLoader implements EventSubscriberInterface, ResetInterface
{
    /**
     * @var array<string, array<string, DocumentConfiguration>>
     */
    private array $configs = [];

    /**
     * @internal
     *
     * @param EntityRepository<CountryCollection> $countryRepository
     * @param EntityRepository<DocumentBaseConfigCollection> $documentConfigRepository
     */
    public function __construct(
        private readonly EntityRepository $documentConfigRepository,
        private readonly EntityRepository $countryRepository
    ) {
    }

    /**
     * @internal
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'document_base_config.written' => 'reset',
        ];
    }

    public function load(string $documentType, string $salesChannelId, Context $context): DocumentConfiguration
    {
        $config = $this->configs[$documentType][$salesChannelId] ?? null;
        if ($config instanceof DocumentConfiguration) {
            return $config;
        }

        $criteria = new Criteria()
            ->addFilter(new EqualsFilter('documentType.technicalName', $documentType))
            ->addAssociation('logo');

        $criteria->getAssociation('salesChannels')
            ->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));

        $documentConfigs = $this->documentConfigRepository->search($criteria, $context)->getEntities();

        $globalConfig = $documentConfigs->filterByProperty('global', true)->first();

        $salesChannelConfig = $documentConfigs->filter(static fn (DocumentBaseConfigEntity $config) => ((int) $config->getSalesChannels()?->count()) > 0)->first();

        $config = DocumentConfigurationFactory::createConfiguration([], $globalConfig, $salesChannelConfig);

        if (Uuid::isValid($config->getCompanyCountryId())) {
            $country = $this->countryRepository->search(new Criteria([$config->getCompanyCountryId()]), $context)->first();

            $config->setCompanyCountry($country);
        }

        $this->configs[$documentType] ??= [];

        return $this->configs[$documentType][$salesChannelId] = $config;
    }

    /**
     * @internal
     */
    public function reset(): void
    {
        $this->configs = [];
    }
}
