<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Registry;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\PaymentSystem\Gateway\DataAbstractionLayer\GatewayConfigCollection;
use Shopwell\Core\PaymentSystem\Gateway\DataAbstractionLayer\GatewayConfigEntity;
use Shopwell\Core\PaymentSystem\Gateway\GatewayFactoryInterface;
use Shopwell\Core\PaymentSystem\Gateway\GatewayInterface;
use Shopwell\Core\PaymentSystem\Gateway\PaymentSystemGatewayException;

#[Package('payment-system')]
class DynamicRegistry implements RegistryInterface
{
    /**
     * @var array<string,GatewayInterface>
     */
    public array $gateways = [];

    /**
     * @internal
     *
     * @param EntityRepository<GatewayConfigCollection> $gatewayConfigRepository
     */
    public function __construct(
        protected readonly GatewayFactoryRegistryInterface $gatewayFactoryRegistry,
        protected readonly EntityRepository $gatewayConfigRepository,
    ) {
    }

    public function getGatewayFactory(string $name): GatewayFactoryInterface
    {
        return $this->gatewayFactoryRegistry->getGatewayFactory($name);
    }

    public function getGatewayFactories(): array
    {
        return $this->gatewayFactoryRegistry->getGatewayFactories();
    }

    public function getGateway(string $name): GatewayInterface
    {
        if (\array_key_exists($name, $this->gateways)) {
            return $this->gateways[$name];
        }
        $criteria = new Criteria()
            ->addFilter(
                new EqualsFilter('name', $name),
                new EqualsFilter('active', true)
            );
        $gatewayConfig = $this->gatewayConfigRepository->search($criteria, Context::createDefaultContext())->getEntities()->first();
        if ($gatewayConfig !== null) {
            $this->gateways[$name] = $this->createGateway($gatewayConfig);
        }
        throw PaymentSystemGatewayException::gatewayNotFound($name);
    }

    private function createGateway(GatewayConfigEntity $config): GatewayInterface
    {
        $gatewayFactory = $this->getGatewayFactory($config->factory);

        return $gatewayFactory->create($config->config);
    }
}
