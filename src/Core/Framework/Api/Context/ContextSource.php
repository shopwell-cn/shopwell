<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Api\Context;

use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[DiscriminatorMap(typeProperty: 'type', mapping: ['system' => SystemSource::class, 'sales-channel' => SalesChannelApiSource::class, 'admin-api' => AdminApiSource::class, 'shop-api' => ShopApiSource::class, 'admin-sales-channel-api' => AdminSalesChannelApiSource::class])]
#[Package('framework')]
interface ContextSource
{
}
