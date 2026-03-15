<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\Renderer;

use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
final class OrderDocumentCriteriaFactory
{
    /**
     * @internal
     */
    private function __construct()
    {
    }

    /**
     * @param array<int, string> $ids
     */
    public static function create(array $ids, string $deepLinkCode = '', ?string $documentType = null): Criteria
    {
        $criteria = new Criteria($ids);

        $criteria->addAssociations([
            'primaryOrderDelivery',
            'lineItems',
            'primaryOrderTransaction.paymentMethod',
            'primaryOrderTransaction.stateMachineState',
            'currency',
            'language.locale',
            'addresses.country',
            'addresses.salutation',
            'addresses.countryState',
            'deliveries.positions',
            'deliveries.shippingMethod',
            'deliveries.shippingOrderAddress.country',
            'deliveries.shippingOrderAddress.countryState',
            'orderCustomer.customer',
            'orderCustomer.salutation',
        ]);

        $criteria->getAssociation('lineItems')->addSorting(new FieldSorting('position'));
        $criteria->getAssociation('deliveries')->addSorting(new FieldSorting('createdAt'));

        if ($documentType) {
            $criteria->addAssociation('documents.documentType');
            $criteria->getAssociation('documents')
                ->addFilter(new EqualsFilter('documentType.technicalName', $documentType))
                ->setLimit(1);
        }

        if ($deepLinkCode !== '') {
            $criteria->addFilter(new EqualsFilter('deepLinkCode', $deepLinkCode));
        }

        return $criteria;
    }
}
