<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Event;

use Shopwell\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopwell\Core\Checkout\Customer\Event\CustomerAccountRecoverRequestEvent;
use Shopwell\Core\Checkout\Customer\Event\CustomerBeforeLoginEvent;
use Shopwell\Core\Checkout\Customer\Event\CustomerDeletedEvent;
use Shopwell\Core\Checkout\Customer\Event\CustomerDoubleOptInRegistrationEvent;
use Shopwell\Core\Checkout\Customer\Event\CustomerGroupRegistrationAccepted;
use Shopwell\Core\Checkout\Customer\Event\CustomerGroupRegistrationDeclined;
use Shopwell\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Shopwell\Core\Checkout\Customer\Event\CustomerLogoutEvent;
use Shopwell\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use Shopwell\Core\Checkout\Customer\Event\DoubleOptInGuestOrderEvent;
use Shopwell\Core\Checkout\Customer\Event\GuestCustomerRegisterEvent;
use Shopwell\Core\Checkout\Order\Event\OrderPaymentMethodChangedEvent;
use Shopwell\Core\Content\MailTemplate\Service\Event\MailBeforeSentEvent;
use Shopwell\Core\Content\MailTemplate\Service\Event\MailBeforeValidateEvent;
use Shopwell\Core\Content\MailTemplate\Service\Event\MailSentEvent;
use Shopwell\Core\Content\Product\SalesChannel\Review\Event\ReviewFormEvent;
use Shopwell\Core\Content\ProductExport\Event\ProductExportLoggingEvent;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\User\Recovery\UserRecoveryRequestEvent;

#[Package('fundamentals@after-sales')]
final class BusinessEvents
{
    public const string CHECKOUT_CUSTOMER_BEFORE_LOGIN = CustomerBeforeLoginEvent::EVENT_NAME;

    public const string CHECKOUT_CUSTOMER_LOGIN = CustomerLoginEvent::EVENT_NAME;

    public const string CHECKOUT_CUSTOMER_LOGOUT = CustomerLogoutEvent::EVENT_NAME;

    public const string CHECKOUT_CUSTOMER_DELETED = CustomerDeletedEvent::EVENT_NAME;

    public const string USER_RECOVERY_REQUEST = UserRecoveryRequestEvent::EVENT_NAME;

    public const string CHECKOUT_ORDER_PLACED = CheckoutOrderPlacedEvent::EVENT_NAME;

    public const string CHECKOUT_ORDER_PAYMENT_METHOD_CHANGED = OrderPaymentMethodChangedEvent::EVENT_NAME;

    public const string CUSTOMER_ACCOUNT_RECOVER_REQUEST = CustomerAccountRecoverRequestEvent::EVENT_NAME;

    public const string CUSTOMER_DOUBLE_OPT_IN_REGISTRATION = CustomerDoubleOptInRegistrationEvent::EVENT_NAME;

    public const string CUSTOMER_GROUP_REGISTRATION_ACCEPTED = CustomerGroupRegistrationAccepted::EVENT_NAME;

    public const string CUSTOMER_GROUP_REGISTRATION_DECLINED = CustomerGroupRegistrationDeclined::EVENT_NAME;

    public const string CUSTOMER_REGISTER = CustomerRegisterEvent::EVENT_NAME;

    public const string DOUBLE_OPT_IN_GUEST_ORDER = DoubleOptInGuestOrderEvent::EVENT_NAME;

    public const string GUEST_CUSTOMER_REGISTER = GuestCustomerRegisterEvent::EVENT_NAME;

    public const string REVIEW_FORM = ReviewFormEvent::EVENT_NAME;

    public const string MAIL_BEFORE_SENT = MailBeforeSentEvent::EVENT_NAME;

    public const string MAIL_BEFORE_VALIDATE = MailBeforeValidateEvent::EVENT_NAME;

    public const string MAIL_SENT = MailSentEvent::EVENT_NAME;

    public const string PRODUCT_EXPORT_LOGGING = ProductExportLoggingEvent::NAME;

    private function __construct()
    {
    }
}
