<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Payment;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;

/**
 * @extends EntityCollection<PaymentMethodEntity>
 */
#[Package('checkout')]
class PaymentMethodCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getPluginIds(): array
    {
        return $this->fmap(fn (PaymentMethodEntity $paymentMethod) => $paymentMethod->getPluginId());
    }

    public function filterByPluginId(string $id): self
    {
        return $this->filter(fn (PaymentMethodEntity $paymentMethod) => $paymentMethod->getPluginId() === $id);
    }

    /**
     * Sorts the selected payment method first
     * If a different default payment method is defined, it will be sorted second
     * All other payment methods keep their respective sorting
     */
    public function sortPaymentMethodsByPreference(SalesChannelContext $context): void
    {
        $ids = array_merge(
            [$context->getSalesChannel()->getPaymentMethodId()],
            $this->getIds(),
        );

        $this->sortByIdArray($ids);
    }

    public function getApiAlias(): string
    {
        return 'payment_method_collection';
    }

    protected function getExpectedClass(): string
    {
        return PaymentMethodEntity::class;
    }
}
