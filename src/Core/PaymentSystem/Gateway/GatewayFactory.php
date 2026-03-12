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
    final protected const string ACTIONS = 'actions';
    final protected const string EXTENSIONS = 'extensions';

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

        $this->buildActions($gateway, $config);

        $gateway->container = $containerBuilder->build();

        $this->buildExtensions($gateway, $config);

        return $gateway;
    }

    /**
     * @return array<string, class-string<ActionInterface>>|list<class-string<ActionInterface>>
     */
    private function getActions(): array
    {
        return [
            CapturePaymentAction::class,
        ];
    }

    /**
     * @return list<ExtensionInterface|class-string<ExtensionInterface>>
     */
    private function getExtensions(): array
    {
        return [
            EndlessCycleDetectorExtension::class,
            EventDispatcherExtension::class,
        ];
    }

    private function buildActions(Gateway $gateway, ArrayStruct $config): void
    {
        $actions = $this->getActions();

        if ($config->has(self::ACTIONS)) {
            $actions = array_merge($actions, $config->get(self::ACTIONS));
        }

        foreach ($actions as $action) {
            if (\is_string($action)) {
                $action = $this->container->get($action);
            }
            $gateway->addAction($action, $action instanceof PrependActionInterface);
        }
    }

    private function buildExtensions(Gateway $gateway, ArrayStruct $config): void
    {
        $extensions = $this->getExtensions();

        if ($config->has(self::EXTENSIONS)) {
            $extensions = array_merge($extensions, $config->get(self::EXTENSIONS));
        }

        foreach ($extensions as $extension) {
            if (\is_string($extension)) {
                $extension = $this->container->get($extension);
            }
            $gateway->addExtension($extension, $extension instanceof PrependExtensionInterface);
        }
    }
}
