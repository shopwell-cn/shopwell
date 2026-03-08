<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Customer\Validation\Constraint;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Package('checkout')]
class CustomerEmailUnique extends Constraint
{
    final public const string CUSTOMER_EMAIL_NOT_UNIQUE = '79d30fe0-febf-421e-ac9b-1bfd5c9007f7';

    protected const array ERROR_NAMES = [
        self::CUSTOMER_EMAIL_NOT_UNIQUE => 'CUSTOMER_EMAIL_NOT_UNIQUE',
    ];

    public string $message;

    public SalesChannelContext $salesChannelContext;

    #[HasNamedArguments]
    public function __construct(
        SalesChannelContext $salesChannelContext,
        string $message = 'The email address {{ email }} is already in use.'
    ) {
        parent::__construct();

        $this->salesChannelContext = $salesChannelContext;
        $this->message = $message;
    }
}
