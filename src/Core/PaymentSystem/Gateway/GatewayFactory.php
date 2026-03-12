<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\ArrayStruct;
use Shopwell\Core\PaymentSystem\Gateway\Action\ActionInterface;
use Shopwell\Core\PaymentSystem\Gateway\Action\CapturePaymentAction;
use Shopwell\Core\PaymentSystem\Gateway\Action\PrependActionInterface;
use Shopwell\Core\PaymentSystem\Gateway\Extension\EndlessCycleDetectorExtension;
use Shopwell\Core\PaymentSystem\Gateway\Extension\EventDispatcherExtension;
use Shopwell\Core\PaymentSystem\Gateway\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[Package('payment-system')]
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
    public function getActions(): array
    {
        return [
            CapturePaymentAction::class,
        ];
    }

    /**
     * @return list<ExtensionInterface|class-string<ExtensionInterface>>
     */
    public function getExtensions(): array
    {
        return [
            EndlessCycleDetectorExtension::class,
            EventDispatcherExtension::class,
        ];
    }

    protected function buildActions(Gateway $gateway): void
    {
        foreach ($this->getActions() as $action) {
            if (\is_string($action)) {
                if (!$this->container->has($action)) {
                    throw PaymentSystemGatewayException::actionServiceNotFound($action);
                }

                $action = $this->container->get($action);
            }
            if (!$action instanceof ActionInterface) {
                throw PaymentSystemGatewayException::invalidAction($action, ActionInterface::class);
            }
            $gateway->addAction($action, $action instanceof PrependActionInterface);
        }
    }

    protected function buildExtensions(Gateway $gateway): void
    {
        foreach ($this->getExtensions() as $extension) {
            if (\is_string($extension)) {
                if (!$this->container->has($extension)) {
                    throw PaymentSystemGatewayException::extensionServiceNotFound($extension);
                }
                $extension = $this->container->get($extension);
            }
            if (!$extension instanceof ExtensionInterface) {
                throw PaymentSystemGatewayException::invalidExtension($extension, ExtensionInterface::class);
            }
            $gateway->addExtension($extension, $extension instanceof PrependExtensionInterface);
        }
    }
}
