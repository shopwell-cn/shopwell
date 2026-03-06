<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Cart\SalesChannel;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route can be used to load the cart
 */
#[Package('checkout')]
abstract class AbstractCartLoadRoute
{
    abstract public function getDecorated(): AbstractCartLoadRoute;

    abstract public function load(Request $request, SalesChannelContext $context): CartResponse;
}
