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

        $usId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $usId, 'iso' => 'US', 'position' => 10, 'iso3' => 'USA', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEn($usId, 'USA'));
        $connection->insert('country_translation', $languageZh($usId, '美国'));

        $deId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $deId, 'iso' => 'DE', 'position' => 10, 'iso3' => 'DEU', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageZh($deId, '德国'));
        $connection->insert('country_translation', $languageEn($deId, 'Germany'));

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

        $gbId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $gbId, 'iso' => 'GB', 'position' => 5, 'iso3' => 'GBR', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageEn($gbId, 'Great Britain'));
        $connection->insert('country_translation', $languageZh($gbId, '英国'));

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
