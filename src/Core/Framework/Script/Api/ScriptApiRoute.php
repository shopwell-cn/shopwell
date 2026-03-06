<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Script\Api;

use Shopwell\Core\Framework\Adapter\Request\RequestParamHelper;
use Shopwell\Core\Framework\Api\Context\AdminApiSource;
use Shopwell\Core\Framework\Api\Controller\Exception\PermissionDeniedException;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Routing\ApiRouteScope;
use Shopwell\Core\Framework\Script\Execution\Script;
use Shopwell\Core\Framework\Script\Execution\ScriptAppInformation;
use Shopwell\Core\Framework\Script\Execution\ScriptExecutor;
use Shopwell\Core\Framework\Script\Execution\ScriptLoader;
use Shopwell\Core\PlatformRequest;
use Shopwell\Core\System\SalesChannel\Api\ResponseFields;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('framework')]
class ScriptApiRoute
{
    public function __construct(
        private readonly ScriptExecutor $executor,
        private readonly ScriptLoader $loader,
        private readonly ScriptResponseEncoder $scriptResponseEncoder
    ) {
    }

    #[Route(path: '/api/script/{hook}', name: 'api.script_endpoint', methods: ['POST', 'GET'], requirements: ['hook' => '.+'])]
    public function execute(string $hook, Request $request, Context $context): Response
    {
        //  blog/update =>  blog-update
        $hook = \str_replace('/', '-', $hook);

        $instance = new ApiHook($hook, $request->request->all(), $context);

        $this->validate($instance, $context);

        // hook: api-{hook}
        $this->executor->execute($instance);

        $fields = new ResponseFields(
            RequestParamHelper::get($request, 'includes', []),
            RequestParamHelper::get($request, 'excludes', []),
        );

        return $this->scriptResponseEncoder->encodeToSymfonyResponse(
            $instance->getScriptResponse(),
            $fields,
            \str_replace('-', '_', 'api_' . $hook . '_response')
        );
    }

    private function validate(ApiHook $hook, Context $context): void
    {
        $scripts = $this->loader->get($hook->getName());

        /** @var Script $script */
        foreach ($scripts as $script) {
            if (!$script->isAppScript()) {
                throw new PermissionDeniedException();
            }

            /** @var ScriptAppInformation $appInfo */
            $appInfo = $script->getScriptAppInformation();

            $source = $context->getSource();
            if ($source instanceof AdminApiSource && $source->getIntegrationId() === $appInfo->getIntegrationId()) {
                // allow access to app endpoints from the integration of the same app
                continue;
            }

            if ($context->isAllowed('app.all')) {
                continue;
            }

            //            $name = $script->getAppName() ?? 'shop-owner-scripts';
            if ($context->isAllowed('app.' . $appInfo->getAppName())) {
                continue;
            }

            throw new PermissionDeniedException();
        }
    }
}
