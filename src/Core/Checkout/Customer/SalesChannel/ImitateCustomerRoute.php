<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Customer\SalesChannel;

use Shopwell\Core\Checkout\Customer\CustomerException;
use Shopwell\Core\Checkout\Customer\ImitateCustomerTokenGenerator;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopwell\Core\Framework\Routing\StoreApiRouteScope;
use Shopwell\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopwell\Core\PlatformRequest;
use Shopwell\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopwell\Core\System\SalesChannel\ContextTokenResponse;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    defaults: [
        PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID],
        PlatformRequest::ATTRIBUTE_CONTEXT_TOKEN_REQUIRED => false,
    ]
)]
#[Package('checkout')]
class ImitateCustomerRoute extends AbstractImitateCustomerRoute
{
    final public const string TOKEN = 'token';

    /**
     * @internal
     */
    public function __construct(
        private readonly AccountService $accountService,
        private readonly ImitateCustomerTokenGenerator $imitateCustomerTokenGenerator,
        private readonly AbstractLogoutRoute $logoutRoute,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
    ) {
    }

    public function getDecorated(): AbstractImitateCustomerRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/account/login/imitate-customer',
        name: 'store-api.account.imitate-customer-login',
        methods: [Request::METHOD_POST]
    )]
    public function imitateCustomerLogin(RequestDataBag $data, SalesChannelContext $context): ContextTokenResponse
    {
        $tokenString = $data->getString(self::TOKEN);

        $token = $this->imitateCustomerTokenGenerator->decode($tokenString);

        if ($token->salesChannelId !== $context->getSalesChannelId()) {
            throw CustomerException::invalidImitationToken($tokenString);
        }

        if ($context->getCustomerId() === $token->customerId) {
            return new ContextTokenResponse($context->getToken());
        }

        if ($context->getCustomer()) {
            $newTokenResponse = $this->logoutRoute->logout($context, new RequestDataBag());

            $context = $this->salesChannelContextFactory->create($newTokenResponse->getToken(), $context->getSalesChannelId());
        }

        $context->setImitatingUserId($token->iss);

        $newToken = $this->accountService->loginById($token->customerId, $context);

        return new ContextTokenResponse($newToken);
    }
}
