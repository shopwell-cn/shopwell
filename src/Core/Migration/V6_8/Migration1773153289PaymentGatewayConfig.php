<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_8;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Defaults;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;
use Shopwell\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('framework')]
class Migration1773153289PaymentGatewayConfig extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1773153289;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
        CREATE TABLE IF NOT EXISTS `payment_gateway_config` (
              `id` BINARY(16) NOT NULL,
              `name` VARCHAR(255) NOT NULL,
              `factory` VARCHAR(255) NOT NULL,
              `active` tinyint(1) NOT NULL DEFAULT 1,
              `config` JSON NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              UNIQUE INDEX `uniq.payment_gateway_config.name` (`name`),
              CONSTRAINT `json.payment_gateway_config.config` CHECK (JSON_VALID(`config`))
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $hasData = $connection->executeQuery('SELECT 1 FROM `payment_gateway_config` LIMIT 1')->fetchAssociative();
        if ($hasData) {
            return;
        }

        $alipay = [
            'sandbox' => true,
            'app_id' => '9021000149657375',
            'private_key' => 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCAaDwqk/vWL3rBuBI6iP+3enLp6L+IFMZC1CAiNhXyH+GkATC1hMznesZ9RPwrjqwYQZ1zqKQzjBolhUgKtoCvm9nJrNKasY52uIet6wXYItOP05YL9pptMy+SCsc3uLiWgc4msXfgQoV5i0ZHLONUu10v0l5MJbpd344Oz/72AUvpoi85oN3neUu7qboe6apJmNlsuWAq47YefQJts8q3fO6RE1GkxXsSJAfP3GCRZzDzfUagzLnXiNPBZr0RKoThdbp2LLTORRmoRXWCHaDQ5iGr7RXHF1u7cHWxIunqT62/MhmlZ6b8nKsu3hV1uoaRMRuOyx894n64WrGIj93hAgMBAAECggEAHHZ+d7WryhNmM5CYwc7iWApVdJH5+rEYLXIV2V+Bx9TXUGJPKL1SKjFl1Bi5iPIsJYhN3XhRRXfVx+FeUbmYltSGFxUu7clT2e8XbC6/ceRuXuA4z1gPbQs+jTHEbQ6OJBaL2rnV56j3KzT7FAXYMk0B3/rSmjB2uh42wAPM8TyNpM6fsMKF5hhDqb6ESHZN8/x/WxlaX20F8GTdtcJ2oBj8+AvKigFUYEpRN6voyWigV4z4373D8j4fAdNmYBGXrAad+fTrkUOHm2+byueQOOC1R6h9MoP7dc05oE/Q7kBGGTesqPFvRlJ7AfZ8oI/JlwOr9w2dkJ+h1Tdozi9wAQKBgQD7O+w+ZvyZk6K+zp/ow7hquX5w8NcVIlLgbocDIET8t2PK4Jn76zkeZwMF34Q9+jmFAl6zlEm7hpWkyNCOjEbV6gSU2vPtQHZJLpO8UF1osMipLEX5QWso+u35dtMdtUaSqStRAHew/NDZDBTf/NZHEsrkDqWyyVNx8U6nkRb5gQKBgQCC19LMIFW65b+TQJCuZNJKrW+6xRMiS2H88U7dcFt3V2N4JxgjInTdWoeYkzC0sSD86dp947Rk91/SJ68dIpwtL0O3Z1NlzKpEknMo9sM2epJo7oBhSNZMafjLUzjQWOS6IwSnU7/dnTiUibWeus89T+OnMigydLbDPfnesX1UYQKBgHDA+w0ioXm3pdHm4CPSO2FCsPm1TYKBrhpl4JhJkkytbc8usE08y5zWdKfdIffC5q/IWYJVKiZMh7q2GcvwLxZwee/ouJMhXDUJ/2oD03hZ5yTt5tWwQ4zX2ZgdeTCbG/pUjElFYXKAdKcE4hWI3w5047Qo0rsD2jyIJVnVJy+BAoGAcZPZRxo2cPNoVfFw5gFczjg5SZ4y2s0m9QOfGveiXjj8flspR65sVY11MQtr9x3e7cwtvqO/fvmBMxMBnj/h5BMoK/dEXJTJpJaf4oo5sHu7xdxqkohAhbpjnlPSeIEBo1YsfvdjEIOejsnvXyb4KFfF3OfcHthToa2VziqyCWECgYAV+k1pN1Eah4D70BTuSOGmhyIfzEW/wIixz6vkf39UNdED5XU61leMHjSenv4CGHO5yHW9NQtyH8DS6cT5vlHy7MbCNkCIuxYkbSDJ8XzSAqSad6KNYPZYm/74zZeto1mOeu78sJPANmAj3X0DPLIRUGbt5OoLqStqnjqfZso/xw==',
            'alipay_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4QFGqrFhDQGlgLwqir7AdHEIfIzXw7vn/WH5xWvL72j0gCygtLUWwrYe2mEtW12KBGu3CG+5kRuHC/ND7WgkhtdpXzMjG8YiVXFHVJqZVzD8DLR0PLC54av2A9QM4R7lcP8TmXMOdnFbeB6pN71u0ipPvNS8q9K4Os+jOZJyJbnjg99UHVkCLAgQ9gQB44A0BLNZ7YHaM7rwkhy8PvOPEBTVACPDjnpIC0Ma/M06OfBj8WeXZJK3pad92BI5wPCL6b61Z9hi2hXnVXNhKIS1dhRUH5fjoPo2nOOTUKV/anY5Xb9lZbDxN5Ypy+8yYMVvIItwPTO1GCffEoXSgHlT9QIDAQAB',
        ];

        $connection->insert('payment_gateway_config', ['id' => Uuid::randomBytes(), 'name' => 'alipay', 'factory' => 'alipay', 'config' => \json_encode($alipay, \JSON_THROW_ON_ERROR), 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('payment_gateway_config', ['id' => Uuid::randomBytes(), 'active' => 0, 'name' => 'wechat', 'factory' => 'wechat', 'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }
}
