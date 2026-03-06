<?php declare(strict_types=1);

namespace Shopwell\Storefront\Page\Product;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Shopwell\Storefront\Page\PageLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
class ProductPageLoadedEvent extends PageLoadedEvent
{
    public function __construct(
        protected ProductPage $page,
        SalesChannelContext $salesChannelContext,
        Request $request,
    ) {
        parent::__construct($salesChannelContext, $request);
    }

    public function getPage(): ProductPage
    {
        return $this->page;
    }
}
