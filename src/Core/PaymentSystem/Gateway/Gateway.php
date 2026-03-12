<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway;

use Psr\Container\ContainerInterface;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;
use Shopwell\Core\PaymentSystem\Gateway\Action\ActionInterface;
use Shopwell\Core\PaymentSystem\Gateway\Exception\ReplyException;
use Shopwell\Core\PaymentSystem\Gateway\Extension\Context;
use Shopwell\Core\PaymentSystem\Gateway\Extension\ExtensionCollection;
use Shopwell\Core\PaymentSystem\Gateway\Extension\ExtensionInterface;

#[Package('payment-system')]
class Gateway implements GatewayInterface
{
    public ContainerInterface $container;

    /**
     * @var list<class-string<ActionInterface>|ActionInterface>
     */
    protected array $actions = [];

    /**
     * @var Context[]
     */
    protected array $stack = [];

    protected ExtensionCollection $extensions;

    public function __construct()
    {
        $this->extensions = new ExtensionCollection();
    }

    public function addAction(ActionInterface $action, bool $forcePrepend = false): void
    {
        $forcePrepend ?
            array_unshift($this->actions, $action) :
            array_push($this->actions, $action);
    }

    public function addExtension(ExtensionInterface $extension, bool $forcePrepend = false): void
    {
        $this->extensions->addExtension($extension, $forcePrepend);
    }

    public function execute(mixed $request, bool $catchReply = false): ?ReplyException
    {
        $context = new Context($this, $request, $this->stack);

        $this->stack[] = $context;

        array_pop($this->stack);
        try {
            $this->extensions->onPreExecute($context);

            if (!$context->action) {
                if (!$action = $this->findActionSupported($context->request)) {
                    throw PaymentSystemGatewayException::requestNotSupported($context->request);
                }

                $context->action = $action;
            }

            $this->extensions->onExecute($context);

            $context->action->execute($request);

            $this->extensions->onPostExecute($context);
        } catch (ReplyException $reply) {
            $context->reply = $reply;

            $this->extensions->onPostExecute($context);

            array_pop($this->stack);

            if ($catchReply && $context->reply) {
                return $context->reply;
            }

            if ($context->reply) {
                throw $context->reply;
            }
        } catch (\Exception $e) {
            $context->exception = $e;

            $this->onPostExecuteWithException($context);
        }

        return null;
    }

    protected function onPostExecuteWithException(Context $context): void
    {
        array_pop($this->stack);

        $exception = $context->exception;

        try {
            $this->extensions->onPostExecute($context);
        } catch (\Exception $e) {
            // logic is similar to one in Symfony's ExceptionListener::onKernelException
            $wrapper = $e;
            while (($prev = $wrapper->getPrevious()) instanceof \Throwable) {
                if ($exception === $wrapper = $prev) {
                    throw $e;
                }
            }

            $prev = new \ReflectionProperty('Exception', 'previous');
            $prev->setValue($wrapper, $exception);

            throw $e;
        }

        if ($context->exception) {
            throw $context->exception;
        }
    }

    protected function findActionSupported(Struct $request): ?ActionInterface
    {
        foreach ($this->actions as $action) {
            if ($action instanceof GatewayAwareInterface) {
                $action->gateway = $this;
            }
            if ($action->supports($request)) {
                return $action;
            }
        }

        return null;
    }
}
