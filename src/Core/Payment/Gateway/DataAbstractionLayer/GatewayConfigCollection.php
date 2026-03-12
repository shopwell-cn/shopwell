<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway\DataAbstractionLayer;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<GatewayConfigEntity>
 */
#[Package('payment-system')]
class GatewayConfigCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'payment_gateway_config_collection';
    }

    protected function getExpectedClass(): string
    {
        return GatewayConfigEntity::class;
    }
}
