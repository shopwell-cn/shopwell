<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Script\Api;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Script\Execution\Awareness\HookServiceFactory;
use Shopwell\Core\Framework\Script\Execution\Awareness\SalesChannelContextAware;
use Shopwell\Core\Framework\Script\Execution\Hook;
use Shopwell\Core\Framework\Script\Execution\Script;
use Shopwell\Storefront\Controller\ScriptController;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 */
#[Package('framework')]
class ScriptResponseFactoryFacadeHookFactory extends HookServiceFactory
{
    public function __construct(
        private readonly RouterInterface $router,
        /**
         * @phpstan-ignore phpat.restrictNamespacesInCore (Storefront dependency is nullable. Don't do that! Will be fixed with https://github.com/shopwell/shopwell/issues/12966)
         */
        private readonly ?ScriptController $scriptController
    ) {
    }

    public function factory(Hook $hook, Script $script): ScriptResponseFactoryFacade
    {
        $salesChannelContext = null;
        if ($hook instanceof SalesChannelContextAware) {
            $salesChannelContext = $hook->getSalesChannelContext();
        }

        return new ScriptResponseFactoryFacade(
            $this->router,
            $this->scriptController,
            $salesChannelContext
        );
    }

    public function getName(): string
    {
        return 'response';
    }
}
