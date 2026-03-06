<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Api\Controller;

use Shopwell\Core\Framework\Api\Context\AdminApiSource;
use Shopwell\Core\Framework\Api\Controller\Exception\PermissionDeniedException;
use Shopwell\Core\Framework\Api\Response\ResponseFactoryInterface;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Routing\ApiRouteScope;
use Shopwell\Core\PlatformRequest;
use Shopwell\Core\System\Integration\IntegrationCollection;
use Shopwell\Core\System\Integration\IntegrationDefinition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('fundamentals@framework')]
class IntegrationController extends AbstractController
{
    /**
     * @internal
     *
     * @param EntityRepository<IntegrationCollection> $integrationRepository
     */
    public function __construct(private readonly EntityRepository $integrationRepository)
    {
    }

    #[Route(
        path: '/api/integration',
        name: 'api.integration.create',
        defaults: [PlatformRequest::ATTRIBUTE_ACL => ['integration:create']],
        methods: [Request::METHOD_POST]
    )]
    public function upsertIntegration(
        ?string $integrationId,
        Request $request,
        Context $context,
        ResponseFactoryInterface $factory
    ): Response {
        $source = $context->getSource();

        $data = $request->request->all();

        // only an admin is allowed to set the admin field
        if ((!$source instanceof AdminApiSource)
            || (!$source->isAdmin()
            && isset($data['admin']))
        ) {
            throw new PermissionDeniedException();
        }

        if (!isset($data['id'])) {
            $data['id'] = null;
        }
        $data['id'] = $integrationId ?: $data['id'];

        $events = $context->scope(Context::SYSTEM_SCOPE, fn (Context $context): EntityWrittenContainerEvent => $this->integrationRepository->upsert([$data], $context));

        $eventIds = $events->getEventByEntityName(IntegrationDefinition::ENTITY_NAME)?->getIds() ?? [];
        $entityId = array_last($eventIds);
        \assert($entityId !== null);

        return $factory->createRedirectResponse($this->integrationRepository->getDefinition(), $entityId, $request, $context);
    }

    #[Route(
        path: '/api/integration/{integrationId}',
        name: 'api.integration.update',
        defaults: [PlatformRequest::ATTRIBUTE_ACL => ['integration:update']],
        methods: [Request::METHOD_PATCH]
    )]
    public function updateIntegration(
        ?string $integrationId,
        Request $request,
        Context $context,
        ResponseFactoryInterface $factory
    ): Response {
        return $this->upsertIntegration($integrationId, $request, $context, $factory);
    }
}
