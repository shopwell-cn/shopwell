<?php declare(strict_types=1);

namespace Shopwell\Storefront\Page;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
class GenericPageLoadedEvent extends PageLoadedEvent
{
    public function __construct(
        protected Page $page,
        SalesChannelContext $salesChannelContext,
        Request $request
    ) {
        parent::__construct($salesChannelContext, $request);
    }

    public function getPage(): Page
    {
        return $this->page;
    }
}
