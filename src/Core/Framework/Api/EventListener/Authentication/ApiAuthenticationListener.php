<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Api\EventListener\Authentication;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Shopwell\Core\Framework\Api\OAuth\SymfonyBearerTokenValidator;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Routing\ApiContextRouteScopeDependant;
use Shopwell\Core\Framework\Routing\KernelListenerPriorities;
use Shopwell\Core\Framework\Routing\RouteScopeCheckTrait;
use Shopwell\Core\Framework\Routing\RouteScopeRegistry;
use Shopwell\Core\Framework\Sso\ShopwellGrantType;
use Shopwell\Core\Framework\Sso\ShopwellPasswordGrantType;
use Shopwell\Core\Framework\Sso\ShopwellRefreshTokenGrantType;
use Shopwell\Core\Framework\Sso\TokenService\ExternalTokenService;
use Shopwell\Core\Framework\Sso\UserService\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
#[Package('framework')]
class ApiAuthenticationListener implements EventSubscriberInterface
{
    use RouteScopeCheckTrait;

    /**
     * @internal
     */
    public function __construct(
        private readonly SymfonyBearerTokenValidator $symfonyBearerTokenValidator,
        private readonly AuthorizationServer $authorizationServer,
        private readonly UserRepositoryInterface $userRepository,
        private readonly RefreshTokenRepositoryInterface $refreshTokenRepository,
        private readonly RouteScopeRegistry $routeScopeRegistry,
        private readonly UserService $userService,
        private readonly ExternalTokenService $tokenService,
        private readonly string $accessTokenTtl = 'PT10M',
        private readonly string $refreshTokenTtl = 'P1W'
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['setupOAuth', 128],
            ],
            KernelEvents::CONTROLLER => [
                ['validateRequest', KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_PRIORITY_AUTH_VALIDATE],
            ],
        ];
    }

    public function setupOAuth(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $accessTokenInterval = new \DateInterval($this->accessTokenTtl);
        $refreshTokenInterval = new \DateInterval($this->refreshTokenTtl);

        $passwordGrant = new ShopwellPasswordGrantType($this->userRepository, $this->refreshTokenRepository, $this->userService);
        $passwordGrant->setRefreshTokenTTL($refreshTokenInterval);

        $refreshTokenGrant = new ShopwellRefreshTokenGrantType($this->refreshTokenRepository, $this->userService, $this->tokenService);
        $refreshTokenGrant->setRefreshTokenTTL($refreshTokenInterval);

        // At this point session is not set $event->getRequest()->getSession()
        $shopwellGrant = new ShopwellGrantType($this->refreshTokenRepository, $this->userService, $this->tokenService);
        $shopwellGrant->setRefreshTokenTTL($refreshTokenInterval);

        $this->authorizationServer->enableGrantType($passwordGrant, $accessTokenInterval);
        $this->authorizationServer->enableGrantType($refreshTokenGrant, $accessTokenInterval);
        $this->authorizationServer->enableGrantType(new ClientCredentialsGrant(), $accessTokenInterval);
        $this->authorizationServer->enableGrantType($shopwellGrant, $accessTokenInterval);
    }

    public function validateRequest(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->get('auth_required', true)) {
            return;
        }

        if (!$this->isRequestScoped($request, ApiContextRouteScopeDependant::class)) {
            return;
        }

        $this->symfonyBearerTokenValidator->validateAuthorization($event->getRequest());
    }

    protected function getScopeRegistry(): RouteScopeRegistry
    {
        return $this->routeScopeRegistry;
    }
}
