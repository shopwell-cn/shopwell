<?php declare(strict_types=1);

namespace Shopwell\Storefront\Checkout\Cart\SalesChannel;

use Shopwell\Core\Checkout\Cart\AbstractCartPersister;
use Shopwell\Core\Checkout\Cart\Cart;
use Shopwell\Core\Checkout\Cart\CartCalculator;
use Shopwell\Core\Checkout\Cart\Error\ErrorCollection;
use Shopwell\Core\Checkout\Cart\SalesChannel\CartService;
use Shopwell\Core\Checkout\Payment\Cart\Error\PaymentMethodBlockedError;
use Shopwell\Core\Checkout\Shipping\Cart\Error\ShippingMethodBlockedError;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopwell\Core\System\SalesChannel\SalesChannel\AbstractContextSwitchRoute;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Shopwell\Storefront\Checkout\Cart\Error\PaymentMethodChangedError;
use Shopwell\Storefront\Checkout\Cart\Error\ShippingMethodChangedError;
use Shopwell\Storefront\Checkout\Payment\BlockedPaymentMethodSwitcher;
use Shopwell\Storefront\Checkout\Shipping\BlockedShippingMethodSwitcher;

#[Package('checkout')]
class StorefrontCartFacade
{
    /**
     * @internal
     */
    public function __construct(
        private readonly CartService $cartService,
        private readonly BlockedShippingMethodSwitcher $blockedShippingMethodSwitcher,
        private readonly BlockedPaymentMethodSwitcher $blockedPaymentMethodSwitcher,
        private readonly AbstractContextSwitchRoute $contextSwitchRoute,
        private readonly CartCalculator $calculator,
        private readonly AbstractCartPersister $cartPersister
    ) {
    }

    public function get(
        string $token,
        SalesChannelContext $originalContext,
        bool $caching = true,
        bool $taxed = false
    ): Cart {
        $originalCart = $this->cartService->getCart($token, $originalContext, $caching, $taxed);
        $cartErrors = $originalCart->getErrors();
        if (!$this->cartContainsBlockedMethods($cartErrors)) {
            return $originalCart;
        }

        // Switch shipping method if blocked
        $contextShippingMethod = $this->blockedShippingMethodSwitcher->switch($cartErrors, $originalContext);

        // Switch payment method if blocked
        $contextPaymentMethod = $this->blockedPaymentMethodSwitcher->switch($cartErrors, $originalContext);

        if ($contextShippingMethod->getId() === $originalContext->getShippingMethod()->getId()
            && $contextPaymentMethod->getId() === $originalContext->getPaymentMethod()->getId()
        ) {
            return $originalCart;
        }

        $updatedContext = clone $originalContext;
        $updatedContext->assign([
            'shippingMethod' => $contextShippingMethod,
            'paymentMethod' => $contextPaymentMethod,
        ]);

        $newCart = $this->calculator->calculate($originalCart, $updatedContext);

        // Recalculated cart successfully unblocked
        if (!$this->cartContainsBlockedMethods($newCart->getErrors())) {
            $this->cartPersister->save($newCart, $updatedContext);
            $this->updateSalesChannelContext($updatedContext, $originalContext);

            return $newCart;
        }

        // Recalculated cart contains one or more blocked shipping/payment method, rollback changes
        $this->removeSwitchNotices($cartErrors);

        return $originalCart;
    }

    private function cartContainsBlockedMethods(ErrorCollection $errors): bool
    {
        foreach ($errors as $error) {
            if ($error instanceof ShippingMethodBlockedError || $error instanceof PaymentMethodBlockedError) {
                return true;
            }
        }

        return false;
    }

    private function updateSalesChannelContext(SalesChannelContext $updatedContext, SalesChannelContext $originalContext): void
    {
        $this->contextSwitchRoute->switchContext(
            new RequestDataBag([
                SalesChannelContextService::SHIPPING_METHOD_ID => $updatedContext->getShippingMethod()->getId(),
                SalesChannelContextService::PAYMENT_METHOD_ID => $updatedContext->getPaymentMethod()->getId(),
            ]),
            $updatedContext,
        );

        $originalContext->assign([
            'shippingMethod' => $updatedContext->getShippingMethod(),
            'paymentMethod' => $updatedContext->getPaymentMethod(),
        ]);

        // inherit rule changes done by CartRuleLoader
        $originalContext->setRuleIds($updatedContext->getRuleIds());
        $originalContext->setAreaRuleIds($updatedContext->getAreaRuleIds());
    }

    /**
     * Remove all PaymentMethodChangedErrors and ShippingMethodChangedErrors from cart
     */
    private function removeSwitchNotices(ErrorCollection $cartErrors): void
    {
        foreach ($cartErrors as $error) {
            if (!$error instanceof ShippingMethodChangedError && !$error instanceof PaymentMethodChangedError) {
                continue;
            }

            if ($error instanceof ShippingMethodChangedError) {
                $cartErrors->add(new ShippingMethodBlockedError(
                    id: $error->getOldShippingMethodId(),
                    name: $error->getOldShippingMethodName(),
                    reason: $error->getReason(),
                ));
            }

            if ($error instanceof PaymentMethodChangedError) {
                $cartErrors->add(new PaymentMethodBlockedError(
                    id: $error->getOldPaymentMethodId(),
                    name: $error->getOldPaymentMethodName(),
                    reason: $error->getReason(),
                ));
            }

            $cartErrors->remove($error->getId());
        }
    }
}
