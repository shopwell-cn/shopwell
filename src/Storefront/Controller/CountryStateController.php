<?php declare(strict_types=1);

namespace Shopwell\Storefront\Controller;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Routing\RoutingException;
use Shopwell\Core\PlatformRequest;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Shopwell\Storefront\Framework\Routing\StorefrontRouteScope;
use Shopwell\Storefront\Pagelet\Country\CountryStateDataPageletLoadedHook;
use Shopwell\Storefront\Pagelet\Country\CountryStateDataPageletLoader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 * Do not use direct or indirect repository calls in a controller. Always use a store-api route to get or put data
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('fundamentals@discovery')]
class CountryStateController extends StorefrontController
{
    /**
     * @internal
     */
    public function __construct(private readonly CountryStateDataPageletLoader $countryStateDataPageletLoader)
    {
    }

    /**
     * @deprecated tag:v6.8.0 - reason:remove-route - Remove POST request and use GET instead only
     */
    #[Route(
        path: '/country/country-state-data',
        name: 'frontend.country.country.data',
        defaults: [
            'XmlHttpRequest' => true,
            PlatformRequest::ATTRIBUTE_HTTP_CACHE => true,
        ],
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    public function getCountryData(Request $request, SalesChannelContext $context): Response
    {
        $countryId = (string) $request->get('countryId');

        if (!$countryId) {
            throw RoutingException::missingRequestParameter('countryId');
        }

        $countryStateDataPagelet = $this->countryStateDataPageletLoader->load($countryId, $request, $context);

        $this->hook(new CountryStateDataPageletLoadedHook($countryStateDataPagelet, $context));

        return new JsonResponse([
            'states' => $countryStateDataPagelet->getStates(),
        ]);
    }
}
