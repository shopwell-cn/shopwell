<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Seo\SeoUrlRoute;

use Shopwell\Core\Framework\DataAbstractionLayer\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\SalesChannel\SalesChannelEntity;

#[Package('inventory')]
interface SeoUrlRouteInterface
{
    public function getConfig(): SeoUrlRouteConfig;

    public function prepareCriteria(Criteria $criteria, SalesChannelEntity $salesChannel): void;

    public function getMapping(Entity $entity, ?SalesChannelEntity $salesChannel): SeoUrlMapping;
}
