<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing;

use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
final class GatewayEvents
{
    public const string GATEWAY_PRE_EXECUTE = 'payment_system.gateway.pre_execute';
    public const string GATEWAY_EXECUTE = 'payment_system.gateway.execute';
    public const string GATEWAY_POST_EXECUTE = 'payment_system.gateway.post_execute';
}
