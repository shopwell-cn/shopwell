<?php declare(strict_types=1);

namespace Shopwell\Core;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
final class SalesChannelRequest
{
    public const string ATTRIBUTE_IS_SALES_CHANNEL_REQUEST = '_is_sales_channel';

    public const string ATTRIBUTE_THEME_ID = 'theme-id';
    public const string ATTRIBUTE_THEME_NAME = 'theme-name';
    public const string ATTRIBUTE_THEME_BASE_NAME = 'theme-base-name';

    public const string ATTRIBUTE_SALES_CHANNEL_MAINTENANCE = 'sw-maintenance';

    public const string ATTRIBUTE_SALES_CHANNEL_MAINTENANCE_IP_WHITLELIST = 'sw-maintenance-ip-whitelist';

    /**
     * domain-resolved attributes
     */
    public const string ATTRIBUTE_DOMAIN_ID = 'sw-domain-id';
    public const string ATTRIBUTE_DOMAIN_LOCALE = '_locale';
    public const string ATTRIBUTE_DOMAIN_SNIPPET_SET_ID = 'sw-snippet-set-id';
    public const string ATTRIBUTE_DOMAIN_CURRENCY_ID = 'sw-currency-id';

    public const string ATTRIBUTE_CANONICAL_LINK = 'sw-canonical-link';

    public const string ATTRIBUTE_STOREFRONT_URL = 'sw-storefront-url';

    private function __construct()
    {
    }
}
