<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\Zugferd;

use horstoeko\zugferd\codelistsenum\ZugferdPaymentMeans;
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdProfiles;
use Shopwell\Core\Checkout\Cart\LineItem\LineItem;
use Shopwell\Core\Checkout\Cart\Price\AmountCalculator;
use Shopwell\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopwell\Core\Checkout\Document\DocumentConfiguration;
use Shopwell\Core\Checkout\Document\DocumentException;
use Shopwell\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryCollection;
use Shopwell\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopwell\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopwell\Core\Checkout\Order\OrderEntity;
use Shopwell\Core\Checkout\Payment\PaymentMethodEntity;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Feature;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('after-sales')]
class ZugferdBuilder
{
    /**
     * @internal
     */
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected AmountCalculator $calculator
    ) {
    }

    public function buildDocument(OrderEntity $order, DocumentConfiguration $config, Context $context): string
    {
        $billingAddress = $order->getAddresses()?->get($order->getBillingAddressId());
        if (!$billingAddress) {
            throw DocumentException::generationError('Billing address not found');
        }

        $customer = $order->getOrderCustomer();
        if (!$customer) {
            throw DocumentException::generationError('Customer not found');
        }

        $deliveryDate = $order->getPrimaryOrderDelivery()?->getShippingDateLatest();
        $transaction = $order->getPrimaryOrderTransaction();

        if (!Feature::isActive('v6.8.0.0')) {
            $deliveryDate = $order->getDeliveries()?->first()?->getShippingDateLatest();
            $transaction = $order->getTransactions()?->last();
        }

        if ($deliveryDate instanceof \DateTimeImmutable) {
            $deliveryDate = \DateTime::createFromImmutable($deliveryDate);
        }

        $taxStatus = $order->getTaxStatus() ?? $order->getPrice()->getTaxStatus();
        $document = (new ZugferdDocument(ZugferdDocumentBuilder::createNew(ZugferdProfiles::PROFILE_XRECHNUNG_3), $taxStatus === CartPrice::TAX_STATE_GROSS))
            ->withBuyerInformation($customer, $billingAddress)
            ->withSellerInformation($config)
            ->withDelivery($order->getDeliveries() ?? new OrderDeliveryCollection())
            ->withTaxes($order->getPrice())
            ->withGeneralOrderData($deliveryDate, $config->getDocumentDate() ?? 'now', $config->getDocumentNumber() ?? '', $order->getCurrency()?->getIsoCode() ?? '')
            ->withBuyerReference($order->getOrderNumber() ?? '');

        $this->addLineItems($document, $order->getLineItems());

        if ($transaction !== null) {
            if ($transaction->getStateMachineState()?->getTechnicalName() === 'paid') {
                $document->withPaidAmount($order->getAmountTotal());
            }

            $paymentMethod = $transaction->getPaymentMethod();
            if ($paymentMethod !== null) {
                $this->addPaymentInfo($document, $config, $paymentMethod);
            }
        }

        $this->eventDispatcher->dispatch(new ZugferdInvoiceGeneratedEvent($document, $order, $config, $context));

        return $document->getContent($order, $this->calculator);
    }

    protected function addLineItems(ZugferdDocument $document, ?OrderLineItemCollection $lineItems, string $parentPosition = ''): self
    {
        if (!$lineItems) {
            return $this;
        }

        foreach ($lineItems as $lineItem) {
            $this->matchByType($document, $lineItem, $parentPosition);
            $this->addLineItems($document, $lineItem->getChildren(), $lineItem->getPosition() . '-');
        }

        return $this;
    }

    protected function matchByType(ZugferdDocument $document, OrderLineItemEntity $lineItem, string $parentPosition = ''): void
    {
        match ($lineItem->getType()) {
            LineItem::PRODUCT_LINE_ITEM_TYPE, LineItem::CUSTOM_LINE_ITEM_TYPE => $document->withProductLineItem($lineItem, $parentPosition),
            LineItem::PROMOTION_LINE_ITEM_TYPE, LineItem::CREDIT_LINE_ITEM_TYPE => $document->withDiscountItem($lineItem),
            default => null,
        };

        $this->eventDispatcher->dispatch(new ZugferdInvoiceItemAddedEvent($document, $lineItem, $parentPosition), 'zugferd-item-added.' . $lineItem->getType());
    }

    private function addPaymentInfo(ZugferdDocument $document, DocumentConfiguration $config, PaymentMethodEntity $paymentMethod): void
    {
        if ($paymentMethod->getTechnicalName() === 'payment_cashpayment') {
            $document->getBuilder()->addDocumentPaymentMean(
                typeCode: (string) ZugferdPaymentMeans::UNTDID_4461_10->value,
                information: $paymentMethod->getName()
            );
        } elseif ($paymentMethod->getTechnicalName() === 'payment_invoicepayment' || $paymentMethod->getTechnicalName() === 'payment_prepayment') {
            $document->getBuilder()->addDocumentPaymentMean(
                typeCode: (string) ZugferdPaymentMeans::UNTDID_4461_30->value,
                information: $paymentMethod->getName(),
                payeeIban: $config->getBankIban(),
                payeeBic: $config->getBankBic()
            );
        }
    }
}
