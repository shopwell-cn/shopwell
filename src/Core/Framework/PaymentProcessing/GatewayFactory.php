<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\Action\ActionInterface;
use Shopwell\Core\Framework\PaymentProcessing\Action\CapturePaymentAction;
use Shopwell\Core\Framework\PaymentProcessing\Action\PrependActionInterface;
use Shopwell\Core\Framework\PaymentProcessing\Extension\EndlessCycleDetectorExtension;
use Shopwell\Core\Framework\PaymentProcessing\Extension\EventDispatcherExtension;
use Shopwell\Core\Framework\PaymentProcessing\Extension\ExtensionInterface;
use Shopwell\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[Package('framework')]
abstract class GatewayFactory implements GatewayFactoryInterface
{
    protected ContainerInterface $container;

    #[Required]
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $previous = $this->container ?? null;
        $this->container = $container;

        return $previous;
    }

    abstract public function configureContainer(ArrayStruct $config): void;

    public function create(array $config): Gateway
    {
        $config = ArrayStruct::ensureArrayStruct($config);

        $this->configureContainer($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions($config->all());

        $gateway = new Gateway();

        $this->buildActions($gateway);

        $gateway->container = $containerBuilder->build();

        $this->buildExtensions($gateway);

        return $gateway;
    }

    /**
     * @return array<string, class-string<ActionInterface>>|list<class-string<ActionInterface>>
     */
    protected function getActions(): array
    {
        return [
            CapturePaymentAction::class,
        ];
    }

    /**
     * @return list<ExtensionInterface|class-string<ExtensionInterface>>
     */
    protected function getExtensions(): array
    {
        return [
            EndlessCycleDetectorExtension::class,
            EventDispatcherExtension::class,
        ];
    }

    private function buildActions(Gateway $gateway): void
    {
        $actions = $this->getActions();

        foreach ($actions as $action) {
            if (\is_string($action)) {
                $action = $this->container->get($action);
            }
            $gateway->addAction($action, $action instanceof PrependActionInterface);
        }
    }

    private function buildExtensions(Gateway $gateway): void
    {
        $extensions = $this->getExtensions();

        foreach ($extensions as $extension) {
            if (\is_string($extension)) {
                $extension = $this->container->get($extension);
            }
            $gateway->addExtension($extension, $extension instanceof PrependExtensionInterface);
        }
    }
}
