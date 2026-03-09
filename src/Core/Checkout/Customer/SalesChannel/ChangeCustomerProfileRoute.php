<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Customer\SalesChannel;

use Shopwell\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopwell\Core\Checkout\Customer\CustomerCollection;
use Shopwell\Core\Checkout\Customer\CustomerDefinition;
use Shopwell\Core\Checkout\Customer\CustomerEntity;
use Shopwell\Core\Checkout\Customer\CustomerEvents;
use Shopwell\Core\Checkout\Customer\Validation\Constraint\CustomerVatIdentification;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\Event\DataMappingEvent;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopwell\Core\Framework\Routing\StoreApiRouteScope;
use Shopwell\Core\Framework\Validation\BuildValidationEvent;
use Shopwell\Core\Framework\Validation\DataBag\DataBag;
use Shopwell\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopwell\Core\Framework\Validation\DataValidationDefinition;
use Shopwell\Core\Framework\Validation\DataValidationFactoryInterface;
use Shopwell\Core\Framework\Validation\DataValidator;
use Shopwell\Core\PlatformRequest;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Shopwell\Core\System\SalesChannel\StoreApiCustomFieldMapper;
use Shopwell\Core\System\SalesChannel\SuccessResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(
    defaults: [
        PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID],
        PlatformRequest::ATTRIBUTE_CONTEXT_TOKEN_REQUIRED => true,
    ]
)]
#[Package('checkout')]
class ChangeCustomerProfileRoute extends AbstractChangeCustomerProfileRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerCollection> $customerRepository
     */
    public function __construct(
        private readonly EntityRepository $customerRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DataValidator $validator,
        private readonly DataValidationFactoryInterface $customerProfileValidationFactory,
        private readonly StoreApiCustomFieldMapper $storeApiCustomFieldMapper,
    ) {
    }

    public function getDecorated(): AbstractChangeCustomerProfileRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/account/change-profile',
        name: 'store-api.account.change-profile',
        defaults: [
            PlatformRequest::ATTRIBUTE_LOGIN_REQUIRED => true,
            PlatformRequest::ATTRIBUTE_LOGIN_REQUIRED_ALLOW_GUEST => true,
        ],
        methods: [Request::METHOD_POST]
    )]
    public function change(RequestDataBag $data, SalesChannelContext $context, CustomerEntity $customer): SuccessResponse
    {
        $validation = $this->customerProfileValidationFactory->update($context);

        if ($data->has('accountType') && $data->getString('accountType') === '') {
            $data->remove('accountType');
        }

        $vatIds = $data->get('vatIds');
        if ($vatIds instanceof RequestDataBag) {
            $vatIds = \array_filter($vatIds->all());
            $data->set('vatIds', $vatIds === [] ? null : $vatIds);
        }

        $this->dispatchValidationEvent($validation, $data, $context->getContext());

        $this->validator->validate($data->all(), $validation);

        $customerData = $data->only('firstName', 'lastName', 'salutationId', 'title', 'company', 'accountType');

        if ($vatIds) {
            $vatIds = $data->get('vatIds');

            if ($vatIds instanceof DataBag) {
                $vatIds = $vatIds->all();
            }

            $customerData['vatIds'] = $vatIds;
        }

        if ($birthday = $this->getBirthday($data)) {
            $customerData['birthday'] = $birthday;
        }

        if ($data->get('customFields') instanceof RequestDataBag) {
            $customerData['customFields'] = $this->storeApiCustomFieldMapper->map(
                CustomerDefinition::ENTITY_NAME,
                $data->get('customFields')
            );
            if ($customerData['customFields'] === []) {
                unset($customerData['customFields']);
            }
        }

        $mappingEvent = new DataMappingEvent($data, $customerData, $context->getContext());
        $this->eventDispatcher->dispatch($mappingEvent, CustomerEvents::MAPPING_CUSTOMER_PROFILE_SAVE);

        $customerData = $mappingEvent->getOutput();

        $customerData['id'] = $customer->getId();

        $this->customerRepository->update([$customerData], $context->getContext());

        return new SuccessResponse();
    }

    private function dispatchValidationEvent(DataValidationDefinition $definition, DataBag $data, Context $context): void
    {
        $validationEvent = new BuildValidationEvent($definition, $data, $context);
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());
    }
    private function getBirthday(DataBag $data): ?\DateTimeInterface
    {
        $birthdayDay = $data->get('birthdayDay');
        $birthdayMonth = $data->get('birthdayMonth');
        $birthdayYear = $data->get('birthdayYear');

        if (!\is_numeric($birthdayDay) || !\is_numeric($birthdayMonth) || !\is_numeric($birthdayYear)) {
            return null;
        }

        return new \DateTime(\sprintf(
            '%s-%s-%s',
            $birthdayYear,
            $birthdayMonth,
            $birthdayDay
        ));
    }
}
