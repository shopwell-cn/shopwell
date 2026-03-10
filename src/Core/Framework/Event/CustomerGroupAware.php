<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Event;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
#[IsFlowEventAware]
interface CustomerGroupAware
{
    public const string CUSTOMER_GROUP_ID = 'customerGroupId';

    public const string CUSTOMER_GROUP = 'customerGroup';

    public function getCustomerGroupId(): string;
}
