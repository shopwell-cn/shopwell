<?php declare(strict_types=1);

namespace Shopwell\Core;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
final class PlatformRequest
{
    /**
     * Response Headers
     */
    public const string HEADER_FRAME_OPTIONS = 'x-frame-options';

    /**
     * Context headers
     */
    public const string HEADER_CONTEXT_TOKEN = 'sw-context-token';
    public const string HEADER_ACCESS_KEY = 'sw-access-key';
    public const string HEADER_LANGUAGE_ID = 'sw-language-id';
    public const string HEADER_CURRENCY_ID = 'sw-currency-id';
    public const string HEADER_INHERITANCE = 'sw-inheritance';
    public const string HEADER_VERSION_ID = 'sw-version-id';
    public const string HEADER_INCLUDE_SEO_URLS = 'sw-include-seo-urls';
    public const string HEADER_INCLUDE_SEARCH_INFO = 'sw-include-search-info';
    public const string HEADER_SKIP_TRIGGER_FLOW = 'sw-skip-trigger-flow';
    public const string HEADER_APP_INTEGRATION_ID = 'sw-app-integration-id';
    public const string HEADER_APP_USER_ID = 'sw-app-user-id';
    public const string HEADER_INDEXING_BEHAVIOR = 'indexing-behavior';
    public const string HEADER_INDEXING_SKIP = 'indexing-skip';
    public const string HEADER_INDEXING_ONLY = 'indexing-only';
    public const string HEADER_FORCE_CACHE_INVALIDATE = 'sw-force-cache-invalidate';

    public const string HEADER_MEASUREMENT_WEIGHT_UNIT = 'sw-measurement-weight-unit';

    public const string HEADER_MEASUREMENT_LENGTH_UNIT = 'sw-measurement-length-unit';

    /**
     * API Expectation headers to check requirements are fulfilled
     */
    public const string HEADER_EXPECT_PACKAGES = 'sw-expect-packages';

    /**
     * Context attributes
     */
    public const string ATTRIBUTE_CONTEXT_OBJECT = 'sw-context';
    public const string ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT = 'sw-sales-channel-context';
    public const string ATTRIBUTE_SALES_CHANNEL_ID = 'sw-sales-channel-id';
    public const string ATTRIBUTE_IMITATING_USER_ID = 'sw-imitating-user-id';

    public const string ATTRIBUTE_ACL = '_acl';
    public const string ATTRIBUTE_CAPTCHA = '_captcha';
    public const string ATTRIBUTE_ROUTE_SCOPE = '_routeScope';
    public const string ATTRIBUTE_ENTITY = '_entity';
    public const string ATTRIBUTE_NO_STORE = '_noStore';
    public const string ATTRIBUTE_HTTP_CACHE = '_httpCache';
    public const string ATTRIBUTE_CONTEXT_TOKEN_REQUIRED = '_contextTokenRequired';
    public const string ATTRIBUTE_LOGIN_REQUIRED = '_loginRequired';
    public const string ATTRIBUTE_LOGIN_REQUIRED_ALLOW_GUEST = '_loginRequiredAllowGuest';
    public const string ATTRIBUTE_IS_ALLOWED_IN_MAINTENANCE = 'allow_maintenance';

    public const array ATTRIBUTE_INTERNAL_ROUTE_PARAMS = [
        self::ATTRIBUTE_CAPTCHA,
        self::ATTRIBUTE_ROUTE_SCOPE,
        self::ATTRIBUTE_ENTITY,
        self::ATTRIBUTE_NO_STORE,
        self::ATTRIBUTE_HTTP_CACHE,
        self::ATTRIBUTE_CONTEXT_TOKEN_REQUIRED,
        self::ATTRIBUTE_LOGIN_REQUIRED,
        self::ATTRIBUTE_LOGIN_REQUIRED_ALLOW_GUEST,
        self::ATTRIBUTE_IS_ALLOWED_IN_MAINTENANCE,
    ];

    /**
     * CSP
     */
    public const string ATTRIBUTE_CSP_NONCE = '_cspNonce';

    /**
     * OAuth attributes
     */
    public const string ATTRIBUTE_OAUTH_ACCESS_TOKEN_ID = 'oauth_access_token_id';
    public const string ATTRIBUTE_OAUTH_CLIENT_ID = 'oauth_client_id';
    public const string ATTRIBUTE_OAUTH_USER_ID = 'oauth_user_id';
    public const string ATTRIBUTE_OAUTH_SCOPES = 'oauth_scopes';

    public const string FALLBACK_SESSION_NAME = 'session-';

    private function __construct()
    {
    }
}
