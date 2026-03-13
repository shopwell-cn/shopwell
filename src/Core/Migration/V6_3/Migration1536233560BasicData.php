<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopwell\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopwell\Core\Checkout\Order\OrderStates;
use Shopwell\Core\Checkout\Payment\Cart\PaymentHandler\CashPayment;
use Shopwell\Core\Checkout\Payment\Cart\PaymentHandler\DefaultPayment;
use Shopwell\Core\Content\Category\CategoryDefinition;
use Shopwell\Core\Defaults;
use Shopwell\Core\Framework\Api\Util\AccessKeyHelper;
use Shopwell\Core\Framework\DataAbstractionLayer\Doctrine\MultiInsertQueryQueue;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;
use Shopwell\Core\Framework\Uuid\Uuid;
use Shopwell\Core\System\DeliveryTime\DeliveryTimeEntity;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233560BasicData extends MigrationStep
{
    private ?string $enGbLanguageId = null;

    public function getCreationTimestamp(): int
    {
        return 1536233560;
    }

    public function update(Connection $connection): void
    {
        $hasData = $connection->executeQuery('SELECT 1 FROM `language` LIMIT 1')->fetchAssociative();
        if ($hasData) {
            return;
        }
        $this->createLanguage($connection);
        $this->createLocale($connection);
        $this->dict($connection);
        $this->createCountry($connection);
        $this->createCurrency($connection);
        $this->createCustomerGroup($connection);
        $this->createPaymentMethod($connection);
        $this->createShippingMethod($connection);
        $this->createDeliveryTimes($connection);
        $this->createTax($connection);
        $this->createRootCategory($connection);
        $this->createSalesChannelTypes($connection);
        $this->createSalesChannel($connection);
        $this->createProductManufacturer($connection);
        $this->createDefaultMediaFolders($connection);
        $this->createDefaultSnippetSets($connection);
        $this->createNumberRanges($connection);
        $this->createOrderStateMachine($connection);
        $this->createOrderDeliveryStateMachine($connection);
        $this->createOrderTransactionStateMachine($connection);
        $this->createSystemConfigOptions($connection);
    }

    private function dict(Connection $connection): void
    {
        $gender = Uuid::randomBytes();

        $languageZh = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageEn = Uuid::fromHexToBytes($this->getEnGbLanguageId());

        $connection->insert('data_dict_group', ['id' => $gender, 'code' => 'gender', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('data_dict_group_translation', [
            'data_dict_group_id' => $gender,
            'language_id' => $languageZh,
            'name' => '性别',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('data_dict_group_translation', [
            'data_dict_group_id' => $gender,
            'language_id' => $languageEn,
            'name' => 'Gender',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $notSpecifiedId = Uuid::randomBytes();
        $connection->insert('data_dict_item', ['id' => $notSpecifiedId, 'group_id' => $gender, 'option_value' => 0, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('data_dict_item_translation', [
            'data_dict_item_id' => $notSpecifiedId,
            'language_id' => $languageZh,
            'name' => '保密',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('data_dict_item_translation', [
            'data_dict_item_id' => $notSpecifiedId,
            'language_id' => $languageEn,
            'name' => 'Not specified',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $maleId = Uuid::randomBytes();
        $connection->insert('data_dict_item', ['id' => $maleId, 'group_id' => $gender, 'option_value' => 1, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('data_dict_item_translation', [
            'data_dict_item_id' => $maleId,
            'language_id' => $languageZh,
            'name' => '男',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('data_dict_item_translation', [
            'data_dict_item_id' => $maleId,
            'language_id' => $languageEn,
            'name' => 'Male',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $femaleId = Uuid::randomBytes();
        $connection->insert('data_dict_item', ['id' => $femaleId, 'group_id' => $gender, 'option_value' => 2, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('data_dict_item_translation', [
            'data_dict_item_id' => $femaleId,
            'language_id' => $languageZh,
            'name' => '女',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('data_dict_item_translation', [
            'data_dict_item_id' => $femaleId,
            'language_id' => $languageEn,
            'name' => 'Female',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function createDefaultSnippetSets(Connection $connection): void
    {
        $queue = new MultiInsertQueryQueue($connection);

        $queue->addInsert('snippet_set', ['id' => Uuid::randomBytes(), 'name' => 'BASE zh-CN', 'base_file' => 'messages.zh-CN', 'iso' => 'zh-CN', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('snippet_set', ['id' => Uuid::randomBytes(), 'name' => 'BASE en-GB', 'base_file' => 'messages.en-GB', 'iso' => 'en-GB', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $queue->execute();
    }

    private function createSystemConfigOptions(Connection $connection): void
    {
        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'core.store.apiUri',
            'configuration_value' => '{"_value": "https://api.shopwell.cn"}',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'core.basicInformation.email',
            'configuration_value' => '{"_value": "doNotReply@localhost"}',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'core.register.minPasswordLength',
            'configuration_value' => '{"_value": 8}',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $id = $connection->fetchOne('SELECT `id` FROM `tax` WHERE `name` = ? LIMIT 1', ['Reduced rate 2']);
        if ($id) {
            $connection->insert('system_config', [
                'id' => Uuid::randomBytes(),
                'configuration_key' => 'core.tax.defaultTaxRate',
                'configuration_value' => json_encode(['_value' => Uuid::fromBytesToHex($id)], \JSON_THROW_ON_ERROR),
                'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }
    }

    private function createOrderTransactionStateMachine(Connection $connection): void
    {
        $stateMachineId = Uuid::randomBytes();

        $openId = Uuid::randomBytes();
        $paidId = Uuid::randomBytes();
        $paidPartiallyId = Uuid::randomBytes();
        $cancelledId = Uuid::randomBytes();
        $remindedId = Uuid::randomBytes();
        $refundedId = Uuid::randomBytes();
        $refundedPartiallyId = Uuid::randomBytes();

        $englishId = Uuid::fromHexToBytes($this->getEnGbLanguageId());
        $chineseId = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $translationZH = ['language_id' => $chineseId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)];
        $translationEN = ['language_id' => $englishId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)];

        // state machine
        $connection->insert('state_machine', [
            'id' => $stateMachineId,
            'technical_name' => OrderTransactionStates::STATE_MACHINE,
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('state_machine_translation', array_merge($translationZH, [
            'state_machine_id' => $stateMachineId,
            'name' => 'Zahlungsstatus',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]));

        $connection->insert('state_machine_translation', array_merge($translationEN, [
            'state_machine_id' => $stateMachineId,
            'name' => 'Payment state',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]));

        // states
        $connection->insert('state_machine_state', ['id' => $openId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_OPEN, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $openId, 'name' => '待支付']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $openId, 'name' => 'Open']));

        $connection->insert('state_machine_state', ['id' => $paidId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_PAID, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $paidId, 'name' => '已支付']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $paidId, 'name' => 'Paid']));

        $connection->insert('state_machine_state', ['id' => $paidPartiallyId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_PARTIALLY_PAID, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $paidPartiallyId, 'name' => '已支付 (部分)']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $paidPartiallyId, 'name' => 'Paid (partially)']));

        $connection->insert('state_machine_state', ['id' => $refundedId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_REFUNDED, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $refundedId, 'name' => '已退款']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $refundedId, 'name' => 'Refunded']));

        $connection->insert('state_machine_state', ['id' => $refundedPartiallyId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_PARTIALLY_REFUNDED, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $refundedPartiallyId, 'name' => '已退款 (部分)']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $refundedPartiallyId, 'name' => 'Refunded (partially)']));

        $connection->insert('state_machine_state', ['id' => $cancelledId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_CANCELLED, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $cancelledId, 'name' => '已取消']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $cancelledId, 'name' => 'Cancelled']));

        $connection->insert('state_machine_state', ['id' => $remindedId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_REMINDED, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $remindedId, 'name' => '已提醒']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $remindedId, 'name' => 'Reminded']));

        // transitions
        // from "open" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'pay', 'from_state_id' => $openId, 'to_state_id' => $paidId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'pay_partially', 'from_state_id' => $openId, 'to_state_id' => $paidPartiallyId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $openId, 'to_state_id' => $cancelledId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'remind', 'from_state_id' => $openId, 'to_state_id' => $remindedId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // from "reminded" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'pay', 'from_state_id' => $remindedId, 'to_state_id' => $paidId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'pay_partially', 'from_state_id' => $remindedId, 'to_state_id' => $paidPartiallyId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $remindedId, 'to_state_id' => $cancelledId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // from "paid_partially" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'remind', 'from_state_id' => $paidPartiallyId, 'to_state_id' => $remindedId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'pay', 'from_state_id' => $paidPartiallyId, 'to_state_id' => $paidId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund_partially', 'from_state_id' => $paidPartiallyId, 'to_state_id' => $refundedPartiallyId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund', 'from_state_id' => $paidPartiallyId, 'to_state_id' => $refundedId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $paidPartiallyId, 'to_state_id' => $cancelledId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // from "paid" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund_partially', 'from_state_id' => $paidId, 'to_state_id' => $refundedPartiallyId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund', 'from_state_id' => $paidId, 'to_state_id' => $refundedId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $paidId, 'to_state_id' => $cancelledId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // from "refunded_partially" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund', 'from_state_id' => $refundedPartiallyId, 'to_state_id' => $refundedId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $refundedPartiallyId, 'to_state_id' => $cancelledId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // from "cancelled" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'reopen', 'from_state_id' => $cancelledId, 'to_state_id' => $openId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund', 'from_state_id' => $cancelledId, 'to_state_id' => $refundedId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund_partially', 'from_state_id' => $cancelledId, 'to_state_id' => $refundedPartiallyId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // set initial state
        $connection->update('state_machine', ['initial_state_id' => $openId], ['id' => $stateMachineId]);
    }

    private function createOrderDeliveryStateMachine(Connection $connection): void
    {
        $stateMachineId = Uuid::randomBytes();
        $openId = Uuid::randomBytes();
        $cancelledId = Uuid::randomBytes();

        $shippedId = Uuid::randomBytes();
        $shippedPartiallyId = Uuid::randomBytes();

        $returnedId = Uuid::randomBytes();
        $returnedPartiallyId = Uuid::randomBytes();

        $englishId = Uuid::fromHexToBytes($this->getEnGbLanguageId());
        $chineseId = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $translationZH = ['language_id' => $chineseId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)];
        $translationEN = ['language_id' => $englishId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)];

        // state machine
        $connection->insert('state_machine', [
            'id' => $stateMachineId,
            'technical_name' => OrderDeliveryStates::STATE_MACHINE,
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('state_machine_translation', array_merge($translationZH, [
            'state_machine_id' => $stateMachineId,
            'name' => '订单物流',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]));

        $connection->insert('state_machine_translation', array_merge($translationEN, [
            'state_machine_id' => $stateMachineId,
            'name' => 'Order state',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]));

        // states
        $connection->insert('state_machine_state', ['id' => $openId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderDeliveryStates::STATE_OPEN, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $openId, 'name' => '待发货']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $openId, 'name' => 'Open']));

        $connection->insert('state_machine_state', ['id' => $shippedId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderDeliveryStates::STATE_SHIPPED, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $shippedId, 'name' => '已发货']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $shippedId, 'name' => 'Shipped']));

        $connection->insert('state_machine_state', ['id' => $shippedPartiallyId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderDeliveryStates::STATE_PARTIALLY_SHIPPED, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $shippedPartiallyId, 'name' => '已发货 (部分)']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $shippedPartiallyId, 'name' => 'Shipped (partially)']));

        $connection->insert('state_machine_state', ['id' => $returnedId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderDeliveryStates::STATE_RETURNED, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $returnedId, 'name' => '已退货']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $returnedId, 'name' => 'Returned']));

        $connection->insert('state_machine_state', ['id' => $returnedPartiallyId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderDeliveryStates::STATE_PARTIALLY_RETURNED, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $returnedPartiallyId, 'name' => '已退货 (部分)']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $returnedPartiallyId, 'name' => 'Returned (partially)']));

        $connection->insert('state_machine_state', ['id' => $cancelledId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderDeliveryStates::STATE_CANCELLED, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $cancelledId, 'name' => '已取消']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $cancelledId, 'name' => 'Cancelled']));

        // transitions
        // from "open" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'ship', 'from_state_id' => $openId, 'to_state_id' => $shippedId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'ship_partially', 'from_state_id' => $openId, 'to_state_id' => $shippedPartiallyId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $openId, 'to_state_id' => $cancelledId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // from "shipped" to *
        //        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'ship', 'from_state_id' => $shippedId, 'to_state_id' => $shippedId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'retour', 'from_state_id' => $shippedId, 'to_state_id' => $returnedId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'retour_partially', 'from_state_id' => $shippedId, 'to_state_id' => $returnedPartiallyId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $shippedId, 'to_state_id' => $cancelledId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // from shipped_partially
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'retour', 'from_state_id' => $shippedPartiallyId, 'to_state_id' => $returnedId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'retour_partially', 'from_state_id' => $shippedPartiallyId, 'to_state_id' => $returnedPartiallyId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'ship', 'from_state_id' => $shippedPartiallyId, 'to_state_id' => $shippedId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $shippedPartiallyId, 'to_state_id' => $cancelledId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // set initial state
        $connection->update('state_machine', ['initial_state_id' => $openId], ['id' => $stateMachineId]);
    }

    private function createOrderStateMachine(Connection $connection): void
    {
        $stateMachineId = Uuid::randomBytes();
        $openId = Uuid::randomBytes();
        $completedId = Uuid::randomBytes();
        $inProgressId = Uuid::randomBytes();
        $canceledId = Uuid::randomBytes();

        $englishId = Uuid::fromHexToBytes($this->getEnGbLanguageId());
        $chineseId = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $translationZH = ['language_id' => $chineseId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)];
        $translationEN = ['language_id' => $englishId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)];

        // state machine
        $connection->insert('state_machine', [
            'id' => $stateMachineId,
            'technical_name' => OrderStates::STATE_MACHINE,
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('state_machine_translation', array_merge($translationZH, [
            'state_machine_id' => $stateMachineId,
            'name' => '订单状态',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]));

        $connection->insert('state_machine_translation', array_merge($translationEN, [
            'state_machine_id' => $stateMachineId,
            'name' => 'Order state',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]));

        // states
        $connection->insert('state_machine_state', ['id' => $openId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderStates::STATE_OPEN, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $openId, 'name' => '待处理']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $openId, 'name' => 'Open']));

        $connection->insert('state_machine_state', ['id' => $completedId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderStates::STATE_COMPLETED, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $completedId, 'name' => '已完成']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $completedId, 'name' => 'Done']));

        $connection->insert('state_machine_state', ['id' => $inProgressId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderStates::STATE_IN_PROGRESS, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $inProgressId, 'name' => '处理中']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $inProgressId, 'name' => 'In progress']));

        $connection->insert('state_machine_state', ['id' => $canceledId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderStates::STATE_CANCELLED, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $canceledId, 'name' => '已取消']));
        $connection->insert('state_machine_state_translation', array_merge($translationEN, ['state_machine_state_id' => $canceledId, 'name' => 'Cancelled']));

        // transitions
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'process', 'from_state_id' => $openId, 'to_state_id' => $inProgressId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $openId, 'to_state_id' => $canceledId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $inProgressId, 'to_state_id' => $canceledId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'complete', 'from_state_id' => $inProgressId, 'to_state_id' => $completedId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'reopen', 'from_state_id' => $canceledId, 'to_state_id' => $openId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'reopen', 'from_state_id' => $completedId, 'to_state_id' => $openId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        // set initial state
        $connection->update('state_machine', ['initial_state_id' => $openId], ['id' => $stateMachineId]);
    }

    private function createNumberRanges(Connection $connection): void
    {
        $definitionNumberRangeTypes = [
            'product' => [
                'id' => Uuid::randomHex(),
                'global' => 1,
                'nameZh' => '产品',
                'nameEn' => 'Product',
            ],
            'order' => [
                'id' => Uuid::randomHex(),
                'global' => 0,
                'nameZh' => '订单',
                'nameEn' => 'Order',
            ],
            'customer' => [
                'id' => Uuid::randomHex(),
                'global' => 0,
                'nameZh' => '客户',
                'nameEn' => 'Customer',
            ],
        ];

        $definitionNumberRanges = [
            'product' => [
                'id' => Uuid::randomHex(),
                'name' => 'Products',
                'nameZh' => '产品',
                'global' => 1,
                'typeId' => $definitionNumberRangeTypes['product']['id'],
                'pattern' => 'SW{n}',
                'start' => 10000,
            ],
            'order' => [
                'id' => Uuid::randomHex(),
                'name' => 'Orders',
                'nameZh' => 'Bestellungen',
                'global' => 1,
                'typeId' => $definitionNumberRangeTypes['order']['id'],
                'pattern' => '{n}',
                'start' => 10000,
            ],
            'customer' => [
                'id' => Uuid::randomHex(),
                'name' => 'Customers',
                'nameZh' => '客户',
                'global' => 1,
                'typeId' => $definitionNumberRangeTypes['customer']['id'],
                'pattern' => '{n}',
                'start' => 10000,
            ],
        ];

        $languageZh = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageEn = Uuid::fromHexToBytes($this->getEnGbLanguageId());

        foreach ($definitionNumberRangeTypes as $typeName => $numberRangeType) {
            $connection->insert(
                'number_range_type',
                [
                    'id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'global' => $numberRangeType['global'],
                    'technical_name' => $typeName,
                    'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
            $connection->insert(
                'number_range_type_translation',
                [
                    'number_range_type_id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'type_name' => $numberRangeType['nameEn'],
                    'language_id' => $languageEn,
                    'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
            $connection->insert(
                'number_range_type_translation',
                [
                    'number_range_type_id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'type_name' => $numberRangeType['nameZh'],
                    'language_id' => $languageZh,
                    'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }

        foreach ($definitionNumberRanges as $numberRange) {
            $connection->insert(
                'number_range',
                [
                    'id' => Uuid::fromHexToBytes($numberRange['id']),
                    'global' => $numberRange['global'],
                    'type_id' => Uuid::fromHexToBytes($numberRange['typeId']),
                    'pattern' => $numberRange['pattern'],
                    'start' => $numberRange['start'],
                    'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
            $connection->insert(
                'number_range_translation',
                [
                    'number_range_id' => Uuid::fromHexToBytes($numberRange['id']),
                    'name' => $numberRange['name'],
                    'language_id' => $languageEn,
                    'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
            $connection->insert(
                'number_range_translation',
                [
                    'number_range_id' => Uuid::fromHexToBytes($numberRange['id']),
                    'name' => $numberRange['nameZh'],
                    'language_id' => $languageZh,
                    'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }
    }

    private function createDefaultMediaFolders(Connection $connection): void
    {
        $queue = new MultiInsertQueryQueue($connection);

        $paymentMethodDefaultFolderId = Uuid::randomBytes();

        $queue->addInsert('media_default_folder', ['id' => Uuid::randomBytes(), 'entity' => 'product', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('media_default_folder', ['id' => Uuid::randomBytes(), 'entity' => 'product_manufacturer', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('media_default_folder', ['id' => Uuid::randomBytes(), 'entity' => 'user', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('media_default_folder', ['id' => Uuid::randomBytes(), 'entity' => 'mail_template', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('media_default_folder', ['id' => Uuid::randomBytes(), 'entity' => 'category', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('media_default_folder', ['id' => $paymentMethodDefaultFolderId, 'entity' => 'payment_method', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('media_default_folder', ['id' => Uuid::randomBytes(), 'entity' => 'customer', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->execute();

        $notCreatedDefaultFolders = $connection->executeQuery('
            SELECT `media_default_folder`.`id` default_folder_id, `media_default_folder`.`entity` entity
            FROM `media_default_folder`
                LEFT JOIN `media_folder` ON `media_folder`.`default_folder_id` = `media_default_folder`.`id`
            WHERE `media_folder`.`id` IS NULL
        ')->fetchAllAssociative();

        foreach ($notCreatedDefaultFolders as $notCreatedDefaultFolder) {
            $this->createDefaultFolder(
                $connection,
                $notCreatedDefaultFolder['default_folder_id'],
                $notCreatedDefaultFolder['entity']
            );
        }
    }

    private function createDefaultFolder(Connection $connection, string $defaultFolderId, string $entity): void
    {
        $connection->transactional(function (Connection $connection) use ($defaultFolderId, $entity): void {
            $configurationId = Uuid::randomBytes();
            $folderId = Uuid::randomBytes();
            $folderName = $this->getMediaFolderName($entity);
            $private = 0;
            if ($entity === 'document') {
                $private = 1;
            }
            $connection->executeStatement('
                INSERT INTO `media_folder_configuration` (`id`, `thumbnail_quality`, `create_thumbnails`, `private`, created_at)
                VALUES (:id, 80, 1, :private, :createdAt)
            ', [
                'id' => $configurationId,
                'createdAt' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                'private' => $private,
            ]);

            $connection->executeStatement('
                INSERT into `media_folder` (`id`, `name`, `default_folder_id`, `media_folder_configuration_id`, `use_parent_configuration`, `child_count`, `created_at`)
                VALUES (:folderId, :folderName, :defaultFolderId, :configurationId, 0, 0, :createdAt)
            ', [
                'folderId' => $folderId,
                'folderName' => $folderName,
                'defaultFolderId' => $defaultFolderId,
                'configurationId' => $configurationId,
                'createdAt' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        });
    }

    private function getMediaFolderName(string $entity): string
    {
        $capitalizedEntityParts = array_map(
            static fn ($part) => ucfirst($part),
            explode('_', $entity)
        );

        return implode(' ', $capitalizedEntityParts) . ' Media';
    }

    private function createProductManufacturer(Connection $connection): void
    {
        $id = Uuid::randomBytes();
        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageEN = Uuid::fromHexToBytes($this->getEnGbLanguageId());
        $versionId = Uuid::fromHexToBytes(Defaults::LIVE_VERSION);

        $connection->insert('product_manufacturer', ['id' => $id, 'version_id' => $versionId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('product_manufacturer_translation', ['product_manufacturer_id' => $id, 'product_manufacturer_version_id' => $versionId, 'language_id' => $languageEN, 'name' => 'Onlishop AG', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('product_manufacturer_translation', ['product_manufacturer_id' => $id, 'product_manufacturer_version_id' => $versionId, 'language_id' => $languageZH, 'name' => 'Onlishop AG', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createSalesChannel(Connection $connection): void
    {
        $currencies = $connection->executeQuery('SELECT id FROM currency')->fetchFirstColumn();
        $languages = $connection->executeQuery('SELECT id FROM language')->fetchFirstColumn();
        $shippingMethods = $connection->executeQuery('SELECT id FROM shipping_method')->fetchFirstColumn();
        $paymentMethods = $connection->executeQuery('SELECT id FROM payment_method')->fetchFirstColumn();
        $defaultPaymentMethod = $connection->executeQuery('SELECT id FROM payment_method WHERE active = 1 ORDER BY `position`')->fetchOne();
        $defaultShippingMethod = $connection->executeQuery('SELECT id FROM shipping_method WHERE active = 1')->fetchOne();
        $countryStatement = $connection->executeQuery('SELECT id FROM country WHERE active = 1 ORDER BY `position`');
        $defaultCountry = $countryStatement->fetchOne();
        $rootCategoryId = $connection->executeQuery('SELECT id FROM category')->fetchOne();

        $id = Uuid::fromHexToBytes('98432def39fc4624b33213a56b8c944d');
        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageEN = Uuid::fromHexToBytes($this->getEnGbLanguageId());

        $connection->insert('sales_channel', [
            'id' => $id,
            'type_id' => Uuid::fromHexToBytes(Defaults::SALES_CHANNEL_TYPE_API),
            'access_key' => AccessKeyHelper::generateAccessKey('sales-channel'),
            'active' => 1,
            'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
            'currency_id' => Uuid::fromHexToBytes(Defaults::CURRENCY),
            'payment_method_id' => $defaultPaymentMethod,
            'shipping_method_id' => $defaultShippingMethod,
            'country_id' => $defaultCountry,
            'navigation_category_id' => $rootCategoryId,
            'navigation_category_version_id' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
            'customer_group_id' => Uuid::fromHexToBytes('cfbd5018d38d41d8adca10d94fc8bdd6'),
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('sales_channel_translation', ['sales_channel_id' => $id, 'language_id' => $languageEN, 'name' => 'Headless', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('sales_channel_translation', ['sales_channel_id' => $id, 'language_id' => $languageZH, 'name' => 'Headless', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // country
        $connection->insert('sales_channel_country', ['sales_channel_id' => $id, 'country_id' => $defaultCountry]);
        $connection->insert('sales_channel_country', ['sales_channel_id' => $id, 'country_id' => $countryStatement->fetchOne()]);

        // currency
        foreach ($currencies as $currency) {
            $connection->insert('sales_channel_currency', ['sales_channel_id' => $id, 'currency_id' => $currency]);
        }

        // language
        foreach ($languages as $language) {
            $connection->insert('sales_channel_language', ['sales_channel_id' => $id, 'language_id' => $language]);
        }

        // shipping methods
        foreach ($shippingMethods as $shippingMethod) {
            $connection->insert('sales_channel_shipping_method', ['sales_channel_id' => $id, 'shipping_method_id' => $shippingMethod]);
        }

        // payment methods
        foreach ($paymentMethods as $paymentMethod) {
            $connection->insert('sales_channel_payment_method', ['sales_channel_id' => $id, 'payment_method_id' => $paymentMethod]);
        }
    }

    private function createSalesChannelTypes(Connection $connection): void
    {
        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageEN = Uuid::fromHexToBytes($this->getEnGbLanguageId());

        $storefront = Uuid::fromHexToBytes(Defaults::SALES_CHANNEL_TYPE_STOREFRONT);
        $storefrontApi = Uuid::fromHexToBytes(Defaults::SALES_CHANNEL_TYPE_API);

        $connection->insert('sales_channel_type', ['id' => $storefront, 'icon_name' => 'regular-storefront', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('sales_channel_type_translation', ['sales_channel_type_id' => $storefront, 'language_id' => $languageEN, 'name' => 'Storefront', 'manufacturer' => 'Shopwell AG', 'description' => 'Sales channel with HTML storefront', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('sales_channel_type_translation', ['sales_channel_type_id' => $storefront, 'language_id' => $languageZH, 'name' => 'Storefront', 'manufacturer' => 'Shopwell AG', 'description' => 'Sales channel mit HTML storefront', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('sales_channel_type', ['id' => $storefrontApi, 'icon_name' => 'regular-shopping-basket', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('sales_channel_type_translation', ['sales_channel_type_id' => $storefrontApi, 'language_id' => $languageEN, 'name' => 'Headless', 'manufacturer' => 'Shopwell AG', 'description' => 'API only sales channel', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('sales_channel_type_translation', ['sales_channel_type_id' => $storefrontApi, 'language_id' => $languageZH, 'name' => 'Headless', 'manufacturer' => 'Shopwell AG', 'description' => 'API only sales channel', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createRootCategory(Connection $connection): void
    {
        $id = Uuid::randomBytes();
        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageEN = Uuid::fromHexToBytes($this->getEnGbLanguageId());
        $versionId = Uuid::fromHexToBytes(Defaults::LIVE_VERSION);

        $connection->insert('category', ['id' => $id, 'version_id' => $versionId, 'type' => CategoryDefinition::TYPE_PAGE, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('category_translation', ['category_id' => $id, 'category_version_id' => $versionId, 'language_id' => $languageEN, 'name' => 'Category #1', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('category_translation', ['category_id' => $id, 'category_version_id' => $versionId, 'language_id' => $languageZH, 'name' => 'Category #1', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createTax(Connection $connection): void
    {
        $tax19 = Uuid::randomBytes();
        $tax7 = Uuid::randomBytes();

        $connection->insert('tax', ['id' => $tax19, 'tax_rate' => 19, 'name' => '19%', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('tax', ['id' => $tax7, 'tax_rate' => 7, 'name' => '7%', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('tax', ['id' => Uuid::randomBytes(), 'tax_rate' => 0, 'name' => 'Reduced rate 2', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createShippingMethod(Connection $connection): void
    {
        $deliveryTimeId = $this->createDeliveryTimes($connection);
        $storePickupId = Uuid::randomBytes();
        $cityDeliveryId = Uuid::randomBytes();

        $ruleId = Uuid::randomBytes();

        $connection->insert('rule', ['id' => $ruleId, 'name' => 'Cart >= 0', 'priority' => 100, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('rule_condition', ['id' => Uuid::randomBytes(), 'rule_id' => $ruleId, 'type' => 'cartCartAmount', 'value' => json_encode(['operator' => '>=', 'amount' => 0]), 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageEN = Uuid::fromHexToBytes($this->getEnGbLanguageId());

        $connection->insert('shipping_method', ['id' => $storePickupId, 'active' => 1, 'technical_name' => 'StorePickup', 'availability_rule_id' => $ruleId, 'delivery_time_id' => $deliveryTimeId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('shipping_method_translation', ['shipping_method_id' => $storePickupId, 'language_id' => $languageEN, 'name' => 'Store Pickup', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('shipping_method_translation', ['shipping_method_id' => $storePickupId, 'language_id' => $languageZH, 'name' => '门店自提', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('shipping_method_price', ['id' => Uuid::randomBytes(), 'shipping_method_id' => $storePickupId, 'calculation' => 1, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('shipping_method', ['id' => $cityDeliveryId, 'active' => 1, 'technical_name' => 'CityDelivery', 'availability_rule_id' => $ruleId, 'delivery_time_id' => $deliveryTimeId, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('shipping_method_translation', ['shipping_method_id' => $cityDeliveryId, 'language_id' => $languageEN, 'name' => 'City Delivery', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('shipping_method_translation', ['shipping_method_id' => $cityDeliveryId, 'language_id' => $languageZH, 'name' => '同城配送', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('shipping_method_price', ['id' => Uuid::randomBytes(), 'shipping_method_id' => $cityDeliveryId, 'calculation' => 1, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createDeliveryTimes(Connection $connection): string
    {
        $languageZh = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageEn = Uuid::fromHexToBytes($this->getEnGbLanguageId());

        $oneToThree = Uuid::randomBytes();
        $twoToFive = Uuid::randomBytes();
        $oneToTwoWeeks = Uuid::randomBytes();
        $threeToFourWeeks = Uuid::randomBytes();

        $connection->insert('delivery_time', ['id' => $oneToThree, 'min' => 1, 'max' => 3, 'unit' => DeliveryTimeEntity::DELIVERY_TIME_DAY, 'created_at' => new \DateTime()->format('Y-m-d H:i:s')]);
        $connection->insert('delivery_time_translation', ['delivery_time_id' => $oneToThree, 'language_id' => $languageEn, 'name' => '1-3 days', 'created_at' => new \DateTime()->format('Y-m-d H:i:s')]);
        $connection->insert('delivery_time_translation', ['delivery_time_id' => $oneToThree, 'language_id' => $languageZh, 'name' => '1-3 天', 'created_at' => new \DateTime()->format('Y-m-d H:i:s')]);
        $connection->insert('delivery_time', ['id' => $twoToFive, 'min' => 2, 'max' => 5, 'unit' => DeliveryTimeEntity::DELIVERY_TIME_DAY, 'created_at' => new \DateTime()->format('Y-m-d H:i:s')]);
        $connection->insert('delivery_time_translation', ['delivery_time_id' => $twoToFive, 'language_id' => $languageEn, 'name' => '2-5 days', 'created_at' => new \DateTime()->format('Y-m-d H:i:s')]);
        $connection->insert('delivery_time_translation', ['delivery_time_id' => $twoToFive, 'language_id' => $languageZh, 'name' => '2-5 天', 'created_at' => new \DateTime()->format('Y-m-d H:i:s')]);
        $connection->insert('delivery_time', ['id' => $oneToTwoWeeks, 'min' => 1, 'max' => 2, 'unit' => DeliveryTimeEntity::DELIVERY_TIME_WEEK, 'created_at' => new \DateTime()->format('Y-m-d H:i:s')]);
        $connection->insert('delivery_time_translation', ['delivery_time_id' => $oneToTwoWeeks, 'language_id' => $languageEn, 'name' => '1-2 weeks', 'created_at' => new \DateTime()->format('Y-m-d H:i:s')]);
        $connection->insert('delivery_time_translation', ['delivery_time_id' => $oneToTwoWeeks, 'language_id' => $languageZh, 'name' => '1-2 周', 'created_at' => new \DateTime()->format('Y-m-d H:i:s')]);
        $connection->insert('delivery_time', ['id' => $threeToFourWeeks, 'min' => 3, 'max' => 4, 'unit' => DeliveryTimeEntity::DELIVERY_TIME_WEEK, 'created_at' => new \DateTime()->format('Y-m-d H:i:s')]);
        $connection->insert('delivery_time_translation', ['delivery_time_id' => $threeToFourWeeks, 'language_id' => $languageEn, 'name' => '3-4 weeks', 'created_at' => new \DateTime()->format('Y-m-d H:i:s')]);
        $connection->insert('delivery_time_translation', ['delivery_time_id' => $threeToFourWeeks, 'language_id' => $languageZh, 'name' => '3-4 周', 'created_at' => new \DateTime()->format('Y-m-d H:i:s')]);

        return $oneToThree;
    }

    private function createPaymentMethod(Connection $connection): void
    {
        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageEN = Uuid::fromHexToBytes($this->getEnGbLanguageId());

        $alipay = Uuid::randomBytes();
        $connection->insert('payment_method', ['id' => $alipay, 'handler_identifier' => DefaultPayment::class, 'position' => 1, 'technical_name' => 'alipay', 'active' => 1, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('payment_method_translation', ['payment_method_id' => $alipay, 'language_id' => $languageEN, 'name' => '支付宝', 'description' => '', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('payment_method_translation', ['payment_method_id' => $alipay, 'language_id' => $languageZH, 'name' => 'Alipay', 'description' => '', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $wechat = Uuid::randomBytes();
        $connection->insert('payment_method', ['id' => $wechat, 'handler_identifier' => DefaultPayment::class, 'position' => 2, 'technical_name' => 'wechat', 'active' => 1, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('payment_method_translation', ['payment_method_id' => $wechat, 'language_id' => $languageEN, 'name' => '微信', 'description' => '', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('payment_method_translation', ['payment_method_id' => $wechat, 'language_id' => $languageZH, 'name' => 'Wechat pay', 'description' => '', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $cashOnDelivery = Uuid::randomBytes();
        $connection->insert('payment_method', ['id' => $cashOnDelivery, 'handler_identifier' => CashPayment::class, 'position' => 3, 'technical_name' => 'cash_delivery', 'active' => 1, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('payment_method_translation', ['payment_method_id' => $cashOnDelivery, 'language_id' => $languageEN, 'name' => '货到付款', 'description' => '', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('payment_method_translation', ['payment_method_id' => $cashOnDelivery, 'language_id' => $languageZH, 'name' => 'Cash on Delivery', 'description' => '', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createCustomerGroup(Connection $connection): void
    {
        $connection->insert('customer_group', ['id' => Uuid::fromHexToBytes('cfbd5018d38d41d8adca10d94fc8bdd6'), 'display_gross' => 1, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('customer_group_translation', ['customer_group_id' => Uuid::fromHexToBytes('cfbd5018d38d41d8adca10d94fc8bdd6'), 'language_id' => Uuid::fromHexToBytes($this->getEnGbLanguageId()), 'name' => 'Standard customer group', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('customer_group_translation', ['customer_group_id' => Uuid::fromHexToBytes('cfbd5018d38d41d8adca10d94fc8bdd6'), 'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM), 'name' => '普通客户组', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createCurrency(Connection $connection): void
    {
        $CNY = Uuid::fromHexToBytes(Defaults::CURRENCY);
        $EUR = Uuid::randomBytes();
        $USD = Uuid::randomBytes();
        $GBP = Uuid::randomBytes();

        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageEN = Uuid::fromHexToBytes($this->getEnGbLanguageId());

        $rounding = [
            'decimals' => 2,
            'roundForNet' => true,
            'interval' => 0.01,
        ];

        $connection->insert('currency', ['id' => $CNY, 'iso_code' => 'CNY', 'item_rounding' => json_encode($rounding), 'total_rounding' => json_encode($rounding), 'factor' => 1, 'symbol' => '¥', 'position' => 1, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $CNY, 'language_id' => $languageEN, 'short_name' => 'CNY', 'name' => 'RMB', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $CNY, 'language_id' => $languageZH, 'short_name' => 'CNY', 'name' => '人民币', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('currency', ['id' => $EUR, 'iso_code' => 'EUR', 'item_rounding' => json_encode($rounding), 'total_rounding' => json_encode($rounding), 'factor' => 0.1205, 'symbol' => '€', 'position' => 2, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $EUR, 'language_id' => $languageEN, 'short_name' => 'EUR', 'name' => 'Euro', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $EUR, 'language_id' => $languageZH, 'short_name' => 'EUR', 'name' => 'Euro', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('currency', ['id' => $USD, 'iso_code' => 'USD', 'item_rounding' => json_encode($rounding), 'total_rounding' => json_encode($rounding), 'factor' => 0.1396, 'symbol' => '$', 'position' => 3, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $USD, 'language_id' => $languageEN, 'short_name' => 'USD', 'name' => 'US-Dollar', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $USD, 'language_id' => $languageZH, 'short_name' => 'USD', 'name' => 'US-Dollar', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('currency', ['id' => $GBP, 'iso_code' => 'GBP', 'item_rounding' => json_encode($rounding), 'total_rounding' => json_encode($rounding), 'factor' => 0.1039, 'symbol' => '£', 'position' => 4, 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $GBP, 'language_id' => $languageEN, 'short_name' => 'GBP', 'name' => 'Pound', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $GBP, 'language_id' => $languageZH, 'short_name' => 'GBP', 'name' => 'Pfund', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createCountry(Connection $connection): void
    {
        $languageEn = fn (string $countryId, string $name) => [
            'language_id' => Uuid::fromHexToBytes($this->getEnGbLanguageId()),
            'name' => $name,
            'country_id' => $countryId,
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        $languageZh = static fn (string $countryId, string $name) => [
            'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
            'name' => $name,
            'country_id' => $countryId,
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];
        $cnId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $cnId, 'iso' => 'CN', 'position' => 1, 'iso3' => 'CNH', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageZh($cnId, '中国'));
        $connection->insert('country_translation', $languageEn($cnId, 'China'));
        $this->createCnRegion($connection, $cnId);

        $usId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $usId, 'iso' => 'US', 'position' => 10, 'iso3' => 'USA', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEn($usId, 'USA'));
        $connection->insert('country_translation', $languageZh($usId, '美国'));

        $deId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $deId, 'iso' => 'DE', 'position' => 10, 'iso3' => 'DEU', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageZh($deId, '德国'));
        $connection->insert('country_translation', $languageEn($deId, 'Germany'));
        $this->createCountryStates($connection, $usId, 'DE');

        $jpId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $jpId, 'iso' => 'JP', 'position' => 10, 'iso3' => 'JPN', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEn($jpId, 'Japan'));
        $connection->insert('country_translation', $languageZh($jpId, '日本'));

        $caId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $caId, 'iso' => 'CA', 'position' => 10, 'iso3' => 'CAN', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEn($caId, 'Canada'));
        $connection->insert('country_translation', $languageZh($caId, '加拿大'));

        $usId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $usId, 'iso' => 'US', 'position' => 10, 'iso3' => 'USA', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEn($usId, 'USA'));
        $connection->insert('country_translation', $languageZh($usId, '美国'));
        $this->createCountryStates($connection, $usId, 'US');

        $gbId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $gbId, 'iso' => 'GB', 'position' => 5, 'iso3' => 'GBR', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEn($gbId, 'Great Britain'));
        $connection->insert('country_translation', $languageZh($gbId, '英国'));
        $this->createCountryStates($connection, $usId, 'GB');

        $frId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $frId, 'iso' => 'FR', 'position' => 10, 'iso3' => 'FRA', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEn($frId, 'France'));
        $connection->insert('country_translation', $languageZh($frId, '法国'));

        $krId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $krId, 'iso' => 'KR', 'position' => 10, 'iso3' => 'KOR', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEn($krId, 'Republic of Korea'));
        $connection->insert('country_translation', $languageZh($krId, '韩国'));

        $auId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $auId, 'iso' => 'AU', 'position' => 10, 'active' => 1, 'iso3' => 'AUS', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEn($auId, 'Australia'));
        $connection->insert('country_translation', $languageZh($auId, '澳大利亚'));

        $sgId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $sgId, 'iso' => 'SG', 'position' => 10, 'active' => 1, 'iso3' => 'SGP', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEn($sgId, 'Singapore'));
        $connection->insert('country_translation', $languageZh($sgId, '新加坡'));

        $ruId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $ruId, 'iso' => 'RU', 'position' => 10, 'iso3' => 'RUS', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEn($ruId, 'Russia'));
        $connection->insert('country_translation', $languageZh($ruId, '俄罗斯'));

        $connection->executeStatement(
            'UPDATE `country` SET
            `customer_tax` = JSON_OBJECT("enabled", 0, "currencyId", :currencyId, "amount", 0);',
            ['currencyId' => Defaults::CURRENCY]
        );
    }

    private function createCountryStates(Connection $connection, string $countryId, string $countryCode): void
    {
        $data = [
            'GB' => [
                'GB-ENG' => '英格兰',
                'GB-NIR' => '北爱尔兰',
                'GB-SCT' => '苏格兰',
                'GB-WLS' => '威尔士',

                'GB-EAW' => '英格兰和威尔士',
                'GB-GBN' => '大不列颠',
                'GB-UKM' => '英国',

                'GB-BKM' => '白金汉郡',
                'GB-CAM' => '剑桥郡',
                'GB-CMA' => '坎布里亚郡',
                'GB-DBY' => '德比郡',
                'GB-DEV' => '德文郡',
                'GB-DOR' => '多塞特郡',
                'GB-ESX' => '东萨塞克斯郡',
                'GB-ESS' => '埃塞克斯郡',
                'GB-GLS' => '格洛斯特郡',
                'GB-HAM' => '汉普郡',
                'GB-HRT' => '赫特福德郡',
                'GB-KEN' => '肯特郡',
                'GB-LAN' => '兰开夏郡',
                'GB-LEC' => '莱斯特郡',
                'GB-LIN' => '林肯郡',
                'GB-NFK' => '诺福克郡',
                'GB-NYK' => '北约克郡',
                'GB-NTH' => '北安普敦郡',
                'GB-NTT' => '诺丁汉郡',
                'GB-OXF' => '牛津郡',
                'GB-SOM' => '萨默塞特郡',
                'GB-STS' => '斯塔福德郡',
                'GB-SFK' => '萨福克郡',
                'GB-SRY' => '萨里郡',
                'GB-WAR' => '沃里克郡',
                'GB-WSX' => '西萨塞克斯郡',
                'GB-WOR' => '伍斯特郡',
                'GB-LND' => '伦敦市',
                'GB-BDG' => '巴金-达格纳姆',
                'GB-BNE' => '巴尼特',
                'GB-BEX' => '贝克斯利',
                'GB-BEN' => '布伦特',
                'GB-BRY' => '布罗姆利',
                'GB-CMD' => '卡姆登',
                'GB-CRY' => '克罗伊登',
                'GB-EAL' => '伊灵',
                'GB-ENF' => '恩菲尔德',
                'GB-GRE' => '格林尼治',
                'GB-HCK' => '哈克尼',
                'GB-HMF' => '哈默史密斯-富勒姆',
                'GB-HRY' => '哈林盖',
                'GB-HRW' => '哈罗',
                'GB-HAV' => '黑弗灵',
                'GB-HIL' => '希灵登',
                'GB-HNS' => '豪恩斯洛',
                'GB-ISL' => '伊斯灵顿',
                'GB-KEC' => '肯辛顿-切尔西',
                'GB-KTT' => '泰晤士河畔金斯顿',
                'GB-LBH' => '兰贝斯',
                'GB-LEW' => '刘易舍姆',
                'GB-MRT' => '默顿',
                'GB-NWM' => '纽汉',
                'GB-RDB' => '雷德布里奇',
                'GB-RIC' => '泰晤士河畔里士满',
                'GB-SWK' => '萨瑟克',
                'GB-STN' => '萨顿',
                'GB-TWH' => '塔村',
                'GB-WFT' => '沃尔瑟姆福里斯特',
                'GB-WND' => '旺兹沃思',
                'GB-WSM' => '威斯敏斯特',
                'GB-BNS' => '巴恩斯利',
                'GB-BIR' => '伯明翰',
                'GB-BOL' => '博尔顿',
                'GB-BRD' => '布拉德福德',
                'GB-BUR' => '贝里',
                'GB-CLD' => '科尔德河谷',
                'GB-COV' => '考文垂',
                'GB-DNC' => '唐卡斯特',
                'GB-DUD' => '达德利',
                'GB-GAT' => '盖茨黑德',
                'GB-KIR' => '柯克利斯',
                'GB-KWL' => '诺斯利',
                'GB-LDS' => '利兹',
                'GB-LIV' => '利物浦',
                'GB-MAN' => '曼彻斯特',
                'GB-NET' => '泰恩河畔纽卡斯尔',
                'GB-NTY' => '北泰恩赛德',
                'GB-OLD' => '奥尔德姆',
                'GB-RCH' => '罗奇代尔',
                'GB-ROT' => '罗瑟勒姆',
                'GB-SHN' => '圣海伦斯',
                'GB-SLF' => '索尔福德',
                'GB-SAW' => '桑德韦尔',
                'GB-SFT' => '塞夫顿',
                'GB-SHF' => '谢菲尔德',
                'GB-SOL' => '索利哈尔',
                'GB-STY' => '南泰恩赛德',
                'GB-SKP' => '斯托克波特',
                'GB-SND' => '桑德兰',
                'GB-TAM' => '泰姆赛德',
                'GB-TRF' => '特拉福德',
                'GB-WKF' => '韦克菲尔德',
                'GB-WLL' => '沃尔索尔',
                'GB-WGN' => '威根',
                'GB-WRL' => '威勒尔',
                'GB-WLV' => '伍尔弗汉普顿',
                'GB-BAS' => '巴斯-东北萨默塞特',
                'GB-BDF' => '贝德福德',
                'GB-BBD' => '布莱克本-达文',
                'GB-BPL' => '布莱克浦',
                'GB-BMH' => '伯恩茅斯',
                'GB-BRC' => '布拉克内尔森林',
                'GB-BNH' => '布莱顿-霍夫',
                'GB-BST' => '布里斯托尔',
                'GB-CBF' => '中贝德福德郡',
                'GB-CHE' => '东柴郡',
                'GB-CHW' => '西柴郡-切斯特',
                'GB-CON' => '康沃尔',
                'GB-DAL' => '达灵顿',
                'GB-DER' => '德比',
                'GB-DUR' => '达勒姆郡',
                'GB-ERY' => '东约克郡',
                'GB-HAL' => '哈尔顿',
                'GB-HPL' => '哈特尔浦',
                'GB-HEF' => '赫里福德郡',
                'GB-IOW' => '怀特岛',
                'GB-IOS' => '锡利群岛',
                'GB-KHL' => '赫尔河畔金斯顿',
                'GB-LCE' => '莱斯特',
                'GB-LUT' => '卢顿',
                'GB-MDW' => '梅德韦',
                'GB-MDB' => '米德尔斯堡',
                'GB-MIK' => '米尔顿凯恩斯',
                'GB-NEL' => '东北林肯郡',
                'GB-NLN' => '北林肯郡',
                'GB-NSM' => '北萨默塞特',
                'GB-NBL' => '诺森伯兰',
                'GB-NGM' => '诺丁汉',
                'GB-PTE' => '彼得伯勒',
                'GB-PLY' => '普利茅斯',
                'GB-POL' => '普尔',
                'GB-POR' => '朴茨茅斯',
                'GB-RDG' => '雷丁',
                'GB-RCC' => '雷德卡-克利夫兰',
                'GB-RUT' => '拉特兰',
                'GB-SHR' => '什罗普郡',
                'GB-SLG' => '斯劳',
                'GB-SGC' => '南格洛斯特郡',
                'GB-STH' => '南安普敦',
                'GB-SOS' => '滨海绍森德',
                'GB-STT' => '蒂斯河畔斯托克顿',
                'GB-STE' => '特伦特河畔斯托克',
                'GB-SWD' => '斯温顿',
                'GB-TFW' => '特尔福德-雷金',
                'GB-THR' => '瑟罗克',
                'GB-TOB' => '托贝',
                'GB-WRT' => '沃灵顿',
                'GB-WBK' => '西伯克郡',
                'GB-WIL' => '威尔特郡',
                'GB-WNM' => '温莎-梅登黑德',
                'GB-WOK' => '沃金厄姆',
                'GB-YOR' => '约克',
                'GB-ANN' => '安特里姆-纽敦阿比',
                'GB-AND' => '阿兹-北唐',
                'GB-ABC' => '阿马-班布里奇-克雷加文',
                'GB-BFS' => '贝尔法斯特',
                'GB-CCG' => '堤道海岸-格伦斯',
                'GB-DRS' => '德里-斯特拉班',
                'GB-FMO' => '弗马纳-奥马',
                'GB-LBC' => '利斯本-卡斯尔雷',
                'GB-MEA' => '中-东安特里姆',
                'GB-MUL' => '中阿尔斯特',
                'GB-NMD' => '纽里-莫恩-唐',
                'GB-ABE' => '阿伯丁市',
                'GB-ABD' => '阿伯丁郡',
                'GB-ANS' => '安格斯',
                'GB-AGB' => '阿盖尔-比特',
                'GB-CLK' => '克拉克曼南郡',
                'GB-DGY' => '邓弗里斯-加洛韦',
                'GB-DND' => '邓迪市',
                'GB-EAY' => '东艾尔郡',
                'GB-EDU' => '东邓巴顿郡',
                'GB-ELN' => '东洛锡安',
                'GB-ERW' => '东伦弗鲁郡',
                'GB-EDH' => '爱丁堡市',
                'GB-ELS' => '埃利安锡尔',
                'GB-FAL' => '福尔柯克',
                'GB-FIF' => '法夫',
                'GB-GLG' => '格拉斯哥市',
                'GB-HLD' => '高地',
                'GB-IVC' => '因弗克莱德',
                'GB-MLN' => '中洛锡安',
                'GB-MRY' => '马里',
                'GB-NAY' => '北艾尔郡',
                'GB-NLK' => '北拉纳克郡',
                'GB-ORK' => '奥克尼群岛',
                'GB-PKN' => '珀斯-金罗斯',
                'GB-RFW' => '伦弗鲁郡',
                'GB-SCB' => '苏格兰边区',
                'GB-ZET' => '设得兰群岛',
                'GB-SAY' => '南艾尔郡',
                'GB-SLK' => '南拉纳克郡',
                'GB-STG' => '斯特灵',
                'GB-WDU' => '西邓巴顿郡',
                'GB-WLN' => '西洛锡安',
                'GB-BGW' => '布莱奈格温特',
                'GB-BGE' => '布里真德',
                'GB-CAY' => '卡菲利',
                'GB-CRF' => '加的夫',
                'GB-CMN' => '卡马森郡',
                'GB-CGN' => '锡尔迪金',
                'GB-CWY' => '康威',
                'GB-DEN' => '登比郡',
                'GB-FLN' => '弗林特郡',
                'GB-GWN' => '格温内斯',
                'GB-AGY' => '安格尔西岛',
                'GB-MTY' => '梅瑟蒂德菲尔',
                'GB-MON' => '蒙茅斯郡',
                'GB-NTL' => '尼思-塔尔伯特港',
                'GB-NWP' => '纽波特',
                'GB-PEM' => '彭布罗克郡',
                'GB-POW' => '波伊斯',
                'GB-RCT' => '朗达卡嫩塔夫',
                'GB-SWA' => '斯旺西',
                'GB-TOF' => '托法恩',
                'GB-VGL' => '格拉摩根谷',
                'GB-WRX' => '雷克瑟姆',
            ],
            'US' => [
                'US-AL' => '阿拉巴马州',
                'US-AK' => '阿拉斯加州',
                'US-AZ' => '亚利桑那州',
                'US-AR' => '阿肯色州',
                'US-CA' => '加利福尼亚州',
                'US-CO' => '科罗拉多州',
                'US-CT' => '康涅狄格州',
                'US-DE' => '特拉华州',
                'US-FL' => '佛罗里达州',
                'US-GA' => '乔治亚州',
                'US-HI' => '夏威夷州',
                'US-ID' => '爱达荷州',
                'US-IL' => '伊利诺伊州',
                'US-IN' => '印第安纳州',
                'US-IA' => '爱荷华州',
                'US-KS' => '堪萨斯州',
                'US-KY' => '肯塔基州',
                'US-LA' => '路易斯安那州',
                'US-ME' => '缅因州',
                'US-MD' => '马里兰州',
                'US-MA' => '马萨诸塞州',
                'US-MI' => '密歇根州',
                'US-MN' => '明尼苏达州',
                'US-MS' => '密西西比州',
                'US-MO' => '密苏里州',
                'US-MT' => '蒙大拿州',
                'US-NE' => '内布拉斯加州',
                'US-NV' => '内华达州',
                'US-NH' => '新罕布什尔州',
                'US-NJ' => '新泽西州',
                'US-NM' => '新墨西哥州',
                'US-NY' => '纽约州',
                'US-NC' => '北卡罗来纳州',
                'US-ND' => '北达科他州',
                'US-OH' => '俄亥俄州',
                'US-OK' => '俄克拉荷马州',
                'US-OR' => '俄勒冈州',
                'US-PA' => '宾夕法尼亚州',
                'US-RI' => '罗得岛州',
                'US-SC' => '南卡罗来纳州',
                'US-SD' => '南达科他州',
                'US-TN' => '田纳西州',
                'US-TX' => '德克萨斯州',
                'US-UT' => '犹他州',
                'US-VT' => '佛蒙特州',
                'US-VA' => '弗吉尼亚州',
                'US-WA' => '华盛顿州',
                'US-WV' => '西弗吉尼亚州',
                'US-WI' => '威斯康星州',
                'US-WY' => '怀俄明州',
                'US-DC' => '哥伦比亚特区',
            ],
            'DE' => [
                'DE-BW' => '巴登-符腾堡州',
                'DE-BY' => '巴伐利亚州',
                'DE-BE' => '柏林州',
                'DE-BB' => '勃兰登堡州',
                'DE-HB' => '不来梅州',
                'DE-HH' => '汉堡州',
                'DE-HE' => '黑森州',
                'DE-NI' => '下萨克森州',
                'DE-MV' => '梅克伦堡-前波莫恩州',
                'DE-NW' => '北莱茵-威斯特法伦州',
                'DE-RP' => '莱茵兰-普法尔茨州',
                'DE-SL' => '萨尔州',
                'DE-SN' => '萨克森州',
                'DE-ST' => '萨克森-安哈尔特州',
                'DE-SH' => '石勒苏益格-荷尔斯泰因州',
                'DE-TH' => '图林根州',
            ],
        ];

        $chineseTranslations = [
            'US' => [
                'US-AL' => 'Alabama',
                'US-AK' => 'Alaska',
                'US-AZ' => 'Arizona',
                'US-AR' => 'Arkansas',
                'US-CA' => 'California',
                'US-CO' => 'Colorado',
                'US-CT' => 'Connecticut',
                'US-DE' => 'Delaware',
                'US-FL' => 'Florida',
                'US-GA' => 'Georgia',
                'US-HI' => 'Hawaii',
                'US-ID' => 'Idaho',
                'US-IL' => 'Illinois',
                'US-IN' => 'Indiana',
                'US-IA' => 'Iowa',
                'US-KS' => 'Kansas',
                'US-KY' => 'Kentucky',
                'US-LA' => 'Louisiana',
                'US-ME' => 'Maine',
                'US-MD' => 'Maryland',
                'US-MA' => 'Massachusetts',
                'US-MI' => 'Michigan',
                'US-MN' => 'Minnesota',
                'US-MS' => 'Mississippi',
                'US-MO' => 'Missouri',
                'US-MT' => 'Montana',
                'US-NE' => 'Nebraska',
                'US-NV' => 'Nevada',
                'US-NH' => 'New Hampshire',
                'US-NJ' => 'New Jersey',
                'US-NM' => 'New Mexico',
                'US-NY' => 'New York',
                'US-NC' => 'North Carolina',
                'US-ND' => 'North Dakota',
                'US-OH' => 'Ohio',
                'US-OK' => 'Oklahoma',
                'US-OR' => 'Oregon',
                'US-PA' => 'Pennsylvania',
                'US-RI' => 'Rhode Island',
                'US-SC' => 'South Carolina',
                'US-SD' => 'South Dakota',
                'US-TN' => 'Tennessee',
                'US-TX' => 'Texas',
                'US-UT' => 'Utah',
                'US-VT' => 'Vermont',
                'US-VA' => 'Virginia',
                'US-WA' => 'Washington',
                'US-WV' => 'West Virginia',
                'US-WI' => 'Wisconsin',
                'US-WY' => 'Wyoming',
                'US-DC' => 'District of Columbia',
            ],
            'DE' => [
                'DE-BW' => 'Baden-Württemberg',
                'DE-BY' => 'Bavaria',
                'DE-BE' => 'Berlin',
                'DE-BB' => 'Brandenburg',
                'DE-HB' => 'Bremen',
                'DE-HH' => 'Hamburg',
                'DE-HE' => 'Hesse',
                'DE-NI' => 'Lower Saxony',
                'DE-MV' => 'Mecklenburg-Western Pomerania',
                'DE-NW' => 'North Rhine-Westphalia',
                'DE-RP' => 'Rhineland-Palatinate',
                'DE-SL' => 'Saarland',
                'DE-SN' => 'Saxony',
                'DE-ST' => 'Saxony-Anhalt',
                'DE-SH' => 'Schleswig-Holstein',
                'DE-TH' => 'Thuringia',
            ],
            'GB' => [
                'GB-ENG' => 'England',
                'GB-NIR' => 'Northern Ireland',
                'GB-SCT' => 'Scotland',
                'GB-WLS' => 'Wales',

                'GB-EAW' => 'England and Wales',
                'GB-GBN' => 'Great Britain',
                'GB-UKM' => 'United Kingdom',

                'GB-BKM' => 'Buckinghamshire',
                'GB-CAM' => 'Cambridgeshire',
                'GB-CMA' => 'Cumbria',
                'GB-DBY' => 'Derbyshire',
                'GB-DEV' => 'Devon',
                'GB-DOR' => 'Dorset',
                'GB-ESX' => 'East Sussex',
                'GB-ESS' => 'Essex',
                'GB-GLS' => 'Gloucestershire',
                'GB-HAM' => 'Hampshire',
                'GB-HRT' => 'Hertfordshire',
                'GB-KEN' => 'Kent',
                'GB-LAN' => 'Lancashire',
                'GB-LEC' => 'Leicestershire',
                'GB-LIN' => 'Lincolnshire',
                'GB-NFK' => 'Norfolk',
                'GB-NYK' => 'North Yorkshire',
                'GB-NTH' => 'Northamptonshire',
                'GB-NTT' => 'Nottinghamshire',
                'GB-OXF' => 'Oxfordshire',
                'GB-SOM' => 'Somerset',
                'GB-STS' => 'Staffordshire',
                'GB-SFK' => 'Suffolk',
                'GB-SRY' => 'Surrey',
                'GB-WAR' => 'Warwickshire',
                'GB-WSX' => 'West Sussex',
                'GB-WOR' => 'Worcestershire',
                'GB-LND' => 'London, City of',
                'GB-BDG' => 'Barking and Dagenham',
                'GB-BNE' => 'Barnet',
                'GB-BEX' => 'Bexley',
                'GB-BEN' => 'Brent',
                'GB-BRY' => 'Bromley',
                'GB-CMD' => 'Camden',
                'GB-CRY' => 'Croydon',
                'GB-EAL' => 'Ealing',
                'GB-ENF' => 'Enfield',
                'GB-GRE' => 'Greenwich',
                'GB-HCK' => 'Hackney',
                'GB-HMF' => 'Hammersmith and Fulham',
                'GB-HRY' => 'Haringey',
                'GB-HRW' => 'Harrow',
                'GB-HAV' => 'Havering',
                'GB-HIL' => 'Hillingdon',
                'GB-HNS' => 'Hounslow',
                'GB-ISL' => 'Islington',
                'GB-KEC' => 'Kensington and Chelsea',
                'GB-KTT' => 'Kingston upon Thames',
                'GB-LBH' => 'Lambeth',
                'GB-LEW' => 'Lewisham',
                'GB-MRT' => 'Merton',
                'GB-NWM' => 'Newham',
                'GB-RDB' => 'Redbridge',
                'GB-RIC' => 'Richmond upon Thames',
                'GB-SWK' => 'Southwark',
                'GB-STN' => 'Sutton',
                'GB-TWH' => 'Tower Hamlets',
                'GB-WFT' => 'Waltham Forest',
                'GB-WND' => 'Wandsworth',
                'GB-WSM' => 'Westminster',
                'GB-BNS' => 'Barnsley',
                'GB-BIR' => 'Birmingham',
                'GB-BOL' => 'Bolton',
                'GB-BRD' => 'Bradford',
                'GB-BUR' => 'Bury',
                'GB-CLD' => 'Calderdale',
                'GB-COV' => 'Coventry',
                'GB-DNC' => 'Doncaster',
                'GB-DUD' => 'Dudley',
                'GB-GAT' => 'Gateshead',
                'GB-KIR' => 'Kirklees',
                'GB-KWL' => 'Knowsley',
                'GB-LDS' => 'Leeds',
                'GB-LIV' => 'Liverpool',
                'GB-MAN' => 'Manchester',
                'GB-NET' => 'Newcastle upon Tyne',
                'GB-NTY' => 'North Tyneside',
                'GB-OLD' => 'Oldham',
                'GB-RCH' => 'Rochdale',
                'GB-ROT' => 'Rotherham',
                'GB-SHN' => 'St. Helens',
                'GB-SLF' => 'Salford',
                'GB-SAW' => 'Sandwell',
                'GB-SFT' => 'Sefton',
                'GB-SHF' => 'Sheffield',
                'GB-SOL' => 'Solihull',
                'GB-STY' => 'South Tyneside',
                'GB-SKP' => 'Stockport',
                'GB-SND' => 'Sunderland',
                'GB-TAM' => 'Tameside',
                'GB-TRF' => 'Trafford',
                'GB-WKF' => 'Wakefield',
                'GB-WLL' => 'Walsall',
                'GB-WGN' => 'Wigan',
                'GB-WRL' => 'Wirral',
                'GB-WLV' => 'Wolverhampton',
                'GB-BAS' => 'Bath and North East Somerset',
                'GB-BDF' => 'Bedford',
                'GB-BBD' => 'Blackburn with Darwen',
                'GB-BPL' => 'Blackpool',
                'GB-BMH' => 'Bournemouth',
                'GB-BRC' => 'Bracknell Forest',
                'GB-BNH' => 'Brighton and Hove',
                'GB-BST' => 'Bristol, City of',
                'GB-CBF' => 'Central Bedfordshire',
                'GB-CHE' => 'Cheshire East',
                'GB-CHW' => 'Cheshire West and Chester',
                'GB-CON' => 'Cornwall',
                'GB-DAL' => 'Darlington',
                'GB-DER' => 'Derby',
                'GB-DUR' => 'Durham County',
                'GB-ERY' => 'East Riding of Yorkshire',
                'GB-HAL' => 'Halton',
                'GB-HPL' => 'Hartlepool',
                'GB-HEF' => 'Herefordshire',
                'GB-IOW' => 'Isle of Wight',
                'GB-IOS' => 'Isles of Scilly',
                'GB-KHL' => 'Kingston upon Hull',
                'GB-LCE' => 'Leicester',
                'GB-LUT' => 'Luton',
                'GB-MDW' => 'Medway',
                'GB-MDB' => 'Middlesbrough',
                'GB-MIK' => 'Milton Keynes',
                'GB-NEL' => 'North East Lincolnshire',
                'GB-NLN' => 'North Lincolnshire',
                'GB-NSM' => 'North Somerset',
                'GB-NBL' => 'Northumberland',
                'GB-NGM' => 'Nottingham',
                'GB-PTE' => 'Peterborough',
                'GB-PLY' => 'Plymouth',
                'GB-POL' => 'Poole',
                'GB-POR' => 'Portsmouth',
                'GB-RDG' => 'Reading',
                'GB-RCC' => 'Redcar and Cleveland',
                'GB-RUT' => 'Rutland',
                'GB-SHR' => 'Shropshire',
                'GB-SLG' => 'Slough',
                'GB-SGC' => 'South Gloucestershire',
                'GB-STH' => 'Southampton',
                'GB-SOS' => 'Southend-on-Sea',
                'GB-STT' => 'Stockton-on-Tees',
                'GB-STE' => 'Stoke-on-Trent',
                'GB-SWD' => 'Swindon',
                'GB-TFW' => 'Telford and Wrekin',
                'GB-THR' => 'Thurrock',
                'GB-TOB' => 'Torbay',
                'GB-WRT' => 'Warrington',
                'GB-WBK' => 'West Berkshire',
                'GB-WIL' => 'Wiltshire',
                'GB-WNM' => 'Windsor and Maidenhead',
                'GB-WOK' => 'Wokingham',
                'GB-YOR' => 'York',
                'GB-ANN' => 'Antrim and Newtownabbey',
                'GB-AND' => 'Ards and North Down',
                'GB-ABC' => 'Armagh, Banbridge and Craigavon',
                'GB-BFS' => 'Belfast',
                'GB-CCG' => 'Causeway Coast and Glens',
                'GB-DRS' => 'Derry and Strabane',
                'GB-FMO' => 'Fermanagh and Omagh',
                'GB-LBC' => 'Lisburn and Castlereagh',
                'GB-MEA' => 'Mid and East Antrim',
                'GB-MUL' => 'Mid Ulster',
                'GB-NMD' => 'Newry, Mourne and Down',
                'GB-ABE' => 'Aberdeen City',
                'GB-ABD' => 'Aberdeenshire',
                'GB-ANS' => 'Angus',
                'GB-AGB' => 'Argyll and Bute',
                'GB-CLK' => 'Clackmannanshire',
                'GB-DGY' => 'Dumfries and Galloway',
                'GB-DND' => 'Dundee City',
                'GB-EAY' => 'East Ayrshire',
                'GB-EDU' => 'East Dunbartonshire',
                'GB-ELN' => 'East Lothian',
                'GB-ERW' => 'East Renfrewshire',
                'GB-EDH' => 'Edinburgh, City of',
                'GB-ELS' => 'Eilean Siar',
                'GB-FAL' => 'Falkirk',
                'GB-FIF' => 'Fife',
                'GB-GLG' => 'Glasgow City',
                'GB-HLD' => 'Highland',
                'GB-IVC' => 'Inverclyde',
                'GB-MLN' => 'Midlothian',
                'GB-MRY' => 'Moray',
                'GB-NAY' => 'North Ayrshire',
                'GB-NLK' => 'North Lanarkshire',
                'GB-ORK' => 'Orkney Islands',
                'GB-PKN' => 'Perth and Kinross',
                'GB-RFW' => 'Renfrewshire',
                'GB-SCB' => 'Scottish Borders, The',
                'GB-ZET' => 'Shetland Islands',
                'GB-SAY' => 'South Ayrshire',
                'GB-SLK' => 'South Lanarkshire',
                'GB-STG' => 'Stirling',
                'GB-WDU' => 'West Dunbartonshire',
                'GB-WLN' => 'West Lothian',
                'GB-BGW' => 'Blaenau Gwent',
                'GB-BGE' => 'Bridgend',
                'GB-CAY' => 'Caerphilly',
                'GB-CRF' => 'Cardiff',
                'GB-CMN' => 'Carmarthenshire',
                'GB-CGN' => 'Ceredigion',
                'GB-CWY' => 'Conwy',
                'GB-DEN' => 'Denbighshire',
                'GB-FLN' => 'Flintshire',
                'GB-GWN' => 'Gwynedd',
                'GB-AGY' => 'Isle of Anglesey',
                'GB-MTY' => 'Merthyr Tydfil',
                'GB-MON' => 'Monmouthshire',
                'GB-NTL' => 'Neath Port Talbot',
                'GB-NWP' => 'Newport',
                'GB-PEM' => 'Pembrokeshire',
                'GB-POW' => 'Powys',
                'GB-RCT' => 'Rhondda, Cynon, Taff',
                'GB-SWA' => 'Swansea',
                'GB-TOF' => 'Torfaen',
                'GB-VGL' => 'Vale of Glamorgan, The',
                'GB-WRX' => 'Wrexham',
            ],
        ];

        foreach ($data[$countryCode] as $isoCode => $name) {
            $storageDate = new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT);
            $id = Uuid::randomBytes();
            $countryStateData = [
                'id' => $id,
                'country_id' => $countryId,
                'short_code' => $isoCode,
                'created_at' => $storageDate,
            ];
            $connection->insert('country_state', $countryStateData);
            $connection->insert('country_state_translation', [
                'language_id' => Uuid::fromHexToBytes($this->getEnGbLanguageId()),
                'country_state_id' => $id,
                'name' => $name,
                'created_at' => $storageDate,
            ]);

            if (isset($chineseTranslations[$countryCode])) {
                $connection->insert('country_state_translation', [
                    'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                    'country_state_id' => $id,
                    'name' => $chineseTranslations[$countryCode][$isoCode],
                    'created_at' => $storageDate,
                ]);
            }
        }
    }

    private function createCnRegion(Connection $connection, string $countryId): void
    {
        $jsonData = file_get_contents(__DIR__ . '/../Fixtures/cn-region.json');
        if ($jsonData === false) {
            return;
        }

        $areas = json_decode($jsonData, true);
        if (!\is_array($areas)) {
            return;
        }

        $storageDate = new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        $stateRows = [];
        $stateTranslationRows = [];
        $regionRows = [];
        $regionTranslationRows = [];

        $queue = [];
        foreach ($areas as $top) {
            $queue[] = ['node' => $top, 'parent_id' => null, 'state_id' => null, 'level' => 1];
        }

        while ($queue !== []) {
            $item = array_shift($queue);
            $node = $item['node'];
            $parentId = $item['parent_id'];
            $stateId = $item['state_id'];
            $level = $item['level'];

            $id = Uuid::randomBytes();
            if ($level === 1) {
                $stateRows[] = [
                    'id' => $id,
                    'country_id' => $countryId,
                    'short_code' => $node['code'],
                    'position' => 1,
                    'active' => 1,
                    'created_at' => $storageDate,
                ];

                $stateTranslationRows[] = [
                    'country_state_id' => $id,
                    'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                    'name' => $node['name'],
                    'created_at' => $storageDate,
                ];

                $stateTranslationRows[] = [
                    'country_state_id' => $id,
                    'language_id' => Uuid::fromHexToBytes($this->getEnGbLanguageId()),
                    'name' => $node['pinyin'] ?? $node['name'],
                    'created_at' => $storageDate,
                ];
            } else {
                $regionRows[] = [
                    'id' => $id,
                    'state_id' => $stateId,
                    'parent_id' => $parentId,
                    'short_code' => $node['code'],
                    'position' => 1,
                    'active' => 1,
                    'lng' => $node['lng'] ?? null,
                    'lat' => $node['lat'] ?? null,
                    'created_at' => $storageDate,
                ];

                $regionTranslationRows[] = [
                    'country_state_region_id' => $id,
                    'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                    'name' => $node['name'],
                    'created_at' => $storageDate,
                ];
                $regionTranslationRows[] = [
                    'country_state_region_id' => $id,
                    'language_id' => Uuid::fromHexToBytes($this->getEnGbLanguageId()),
                    'name' => $node['pinyin'] ?? $node['name'],
                    'created_at' => $storageDate,
                ];
            }

            if (isset($node['children']) && \is_array($node['children'])) {
                foreach ($node['children'] as $child) {
                    $queue[] = [
                        'node' => $child,
                        'parent_id' => $level === 1 ? null : $id,
                        'state_id' => $level === 1 ? $id : $stateId,
                        'level' => $level + 1,
                    ];
                }
            }
        }

        $connection->beginTransaction();
        try {
            if ($stateRows !== []) {
                $chunks = array_chunk($stateRows, 500);
                foreach ($chunks as $chunk) {
                    $sql = 'INSERT INTO country_state (id, country_id, short_code, position, active, created_at) VALUES ';
                    $placeholders = [];
                    $params = [];
                    foreach ($chunk as $row) {
                        $placeholders[] = '(?, ?, ?, ?, ?, ?)';
                        $params[] = $row['id'];
                        $params[] = $row['country_id'];
                        $params[] = $row['short_code'];
                        $params[] = $row['position'];
                        $params[] = $row['active'];
                        $params[] = $row['created_at'];
                    }
                    $sql .= implode(', ', $placeholders);
                    $connection->executeStatement($sql, $params);
                }
            }

            if ($stateTranslationRows !== []) {
                $chunks = array_chunk($stateTranslationRows, 1000);
                foreach ($chunks as $chunk) {
                    $sql = 'INSERT INTO country_state_translation (country_state_id, language_id, name, created_at) VALUES ';
                    $placeholders = [];
                    $params = [];
                    foreach ($chunk as $row) {
                        $placeholders[] = '(?, ?, ?, ?)';
                        $params[] = $row['country_state_id'];
                        $params[] = $row['language_id'];
                        $params[] = $row['name'];
                        $params[] = $row['created_at'];
                    }
                    $sql .= implode(', ', $placeholders);
                    $connection->executeStatement($sql, $params);
                }
            }

            if ($regionRows !== []) {
                $chunks = array_chunk($regionRows, 500);
                foreach ($chunks as $chunk) {
                    $sql = 'INSERT INTO country_state_region (id, state_id, parent_id, short_code, position, active, lng, lat, created_at) VALUES ';
                    $placeholders = [];
                    $params = [];
                    foreach ($chunk as $row) {
                        $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?, ?)';
                        $params[] = $row['id'];
                        $params[] = $row['state_id'];
                        $params[] = $row['parent_id'];
                        $params[] = $row['short_code'];
                        $params[] = $row['position'];
                        $params[] = $row['active'];
                        $params[] = $row['lng'];
                        $params[] = $row['lat'];
                        $params[] = $row['created_at'];
                    }
                    $sql .= implode(', ', $placeholders);
                    $connection->executeStatement($sql, $params);
                }
            }

            if ($regionTranslationRows !== []) {
                $chunks = array_chunk($regionTranslationRows, 1000);
                foreach ($chunks as $chunk) {
                    $sql = 'INSERT INTO country_state_region_translation (country_state_region_id, language_id, name, created_at) VALUES ';
                    $placeholders = [];
                    $params = [];
                    foreach ($chunk as $row) {
                        $placeholders[] = '(?, ?, ?, ?)';
                        $params[] = $row['country_state_region_id'];
                        $params[] = $row['language_id'];
                        $params[] = $row['name'];
                        $params[] = $row['created_at'];
                    }
                    $sql .= implode(', ', $placeholders);
                    $connection->executeStatement($sql, $params);
                }
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    private function createLocale(Connection $connection): void
    {
        $localeData = include __DIR__ . '/../../locales.php';

        $queue = new MultiInsertQueryQueue($connection);
        $languageEn = Uuid::fromHexToBytes($this->getEnGbLanguageId());
        $languageZh = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        foreach ($localeData as $locale) {
            if (\in_array($locale['locale'], ['en-GB', 'zh-CN'], true)) {
                continue;
            }

            $localeId = Uuid::randomBytes();

            $queue->addInsert(
                'locale',
                ['id' => $localeId, 'code' => $locale['locale'], 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]
            );

            $queue->addInsert(
                'locale_translation',
                [
                    'locale_id' => $localeId,
                    'language_id' => $languageEn,
                    'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    'name' => $locale['name']['en-GB'],
                    'territory' => $locale['territory']['en-GB'],
                ]
            );

            $queue->addInsert(
                'locale_translation',
                [
                    'locale_id' => $localeId,
                    'language_id' => $languageZh,
                    'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    'name' => $locale['name']['zh-CN'],
                    'territory' => $locale['territory']['zh-CN'],
                ]
            );
        }

        $queue->execute();
    }

    private function createLanguage(Connection $connection): void
    {
        $localeEn = Uuid::randomBytes();
        $localeZh = Uuid::randomBytes();
        $languageZh = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageEn = Uuid::fromHexToBytes($this->getEnGbLanguageId());

        $connection->insert('locale', ['id' => $localeEn, 'code' => 'en-GB', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('locale', ['id' => $localeZh, 'code' => 'zh-CN', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // third translations
        $connection->insert('language', [
            'id' => $languageZh,
            'name' => '中文',
            'locale_id' => $localeZh,
            'translation_code_id' => $localeZh,
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('language', [
            'id' => $languageEn,
            'name' => 'English',
            'locale_id' => $localeEn,
            'translation_code_id' => $localeEn,
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('locale_translation', [
            'locale_id' => $localeZh,
            'language_id' => $languageZh,
            'name' => '中文',
            'territory' => '中国',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('locale_translation', [
            'locale_id' => $localeZh,
            'language_id' => $languageEn,
            'name' => 'Chinese',
            'territory' => 'China',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        // en-GB translations
        $connection->insert('locale_translation', [
            'locale_id' => $localeEn,
            'language_id' => $languageZh,
            'name' => '英语',
            'territory' => '英国',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('locale_translation', [
            'locale_id' => $localeEn,
            'language_id' => $languageEn,
            'name' => 'English',
            'territory' => 'United Kingdom',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function getEnGbLanguageId(): string
    {
        if (!$this->enGbLanguageId) {
            $this->enGbLanguageId = Uuid::randomHex();
        }

        return $this->enGbLanguageId;
    }
}
