<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Defaults;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;
use Shopwell\Core\Framework\Uuid\Uuid;
use Shopwell\Core\System\Tax\Aggregate\TaxRuleType\TaxRuleTypeDefinition;
use Shopwell\Core\System\Tax\TaxRuleType\EntireCountryRuleTypeFilter;
use Shopwell\Core\System\Tax\TaxRuleType\IndividualStatesRuleTypeFilter;
use Shopwell\Core\System\Tax\TaxRuleType\ZipCodeRangeRuleTypeFilter;
use Shopwell\Core\System\Tax\TaxRuleType\ZipCodeRuleTypeFilter;

/**
 * @internal
 */
#[Package('framework')]
class Migration1572193798TaxRule extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1572193798;
    }

    public function update(Connection $connection): void
    {
        $this->createTables($connection);
        $this->addTaxRuleTypes($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    public function createTables(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `tax_rule_type` (
              `id` binary(16) NOT NULL,
              `technical_name` varchar(255) NOT NULL,
              `position` int(11) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.technical_name` (`technical_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        $connection->executeStatement('
            CREATE TABLE `tax_rule_type_translation` (
              `tax_rule_type_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `type_name` varchar(255) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`tax_rule_type_id`,`language_id`),
              KEY `fk.tax_rule_type_translation.language_id` (`language_id`),
              CONSTRAINT `fk.tax_rule_type_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.tax_rule_type_translation.tax_rule_type_id` FOREIGN KEY (`tax_rule_type_id`) REFERENCES `tax_rule_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        $connection->executeStatement('
            CREATE TABLE `tax_rule` (
              `id` binary(16) NOT NULL,
              `tax_id` binary(16) NOT NULL,
              `tax_rule_type_id` binary(16) NOT NULL,
              `country_id` binary(16) NOT NULL,
              `tax_rate` double(10,3) DEFAULT NULL,
              `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `active_from` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.tax_rule.tax_id` (`tax_id`),
              KEY `fk.tax_rule.tax_area_rule_type_id` (`tax_rule_type_id`),
              KEY `fk.tax_rule.country_id` (`country_id`),
              CONSTRAINT `fk.tax_rule.country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`),
              CONSTRAINT `fk.tax_rule.tax_area_rule_type_id` FOREIGN KEY (`tax_rule_type_id`) REFERENCES `tax_rule_type` (`id`),
              CONSTRAINT `fk.tax_rule.tax_id` FOREIGN KEY (`tax_id`) REFERENCES `tax` (`id`),
              CONSTRAINT `json.tax_rule.data` CHECK (json_valid(`data`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    private function addTaxRuleTypes(Connection $connection): void
    {
        $languageIdEn = $this->getLocaleId($connection, 'en-GB');
        $languageIdZh = $this->getLocaleId($connection, 'zh-CN');
        $languageSystem = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $dataZh = [
            ZipCodeRuleTypeFilter::TECHNICAL_NAME => [
                'type_name' => '邮政编码',
            ],
            ZipCodeRangeRuleTypeFilter::TECHNICAL_NAME => [
                'type_name' => '邮政编码范围',
            ],
            IndividualStatesRuleTypeFilter::TECHNICAL_NAME => [
                'type_name' => '省/州',
            ],
            EntireCountryRuleTypeFilter::TECHNICAL_NAME => [
                'type_name' => '国家',
            ],
        ];

        $dataEn = [
            ZipCodeRuleTypeFilter::TECHNICAL_NAME => [
                'type_name' => 'Zip Code',
            ],
            ZipCodeRangeRuleTypeFilter::TECHNICAL_NAME => [
                'type_name' => 'Zip Code Range',
            ],
            IndividualStatesRuleTypeFilter::TECHNICAL_NAME => [
                'type_name' => 'Individual States',
            ],
            EntireCountryRuleTypeFilter::TECHNICAL_NAME => [
                'type_name' => 'Entire Country',
            ],
        ];

        foreach (
            [
                ZipCodeRuleTypeFilter::TECHNICAL_NAME,
                ZipCodeRangeRuleTypeFilter::TECHNICAL_NAME,
                IndividualStatesRuleTypeFilter::TECHNICAL_NAME,
                EntireCountryRuleTypeFilter::TECHNICAL_NAME,
            ] as $position => $technicalName
        ) {
            $typeId = Uuid::randomBytes();
            $typeData = [
                'id' => $typeId,
                'technical_name' => $technicalName,
                'position' => $position,
                'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ];
            $connection->insert(TaxRuleTypeDefinition::ENTITY_NAME, $typeData);

            if (!\in_array($languageSystem, [$languageIdZh, $languageIdEn], true)) {
                $this->insertTranslation($connection, $dataEn[$technicalName], $typeId, $languageSystem);
            }

            $this->insertTranslation($connection, $dataEn[$technicalName], $typeId, $languageIdEn);
            $this->insertTranslation($connection, $dataZh[$technicalName], $typeId, $languageIdZh);
        }
    }

    private function getLocaleId(Connection $connection, string $code): ?string
    {
        $result = $connection->fetchOne(
            '
            SELECT lang.id
            FROM language lang
            INNER JOIN locale loc ON lang.translation_code_id = loc.id
            AND loc.code = :code',
            [
                'code' => $code,
            ]
        );

        if ($result === false) {
            return null;
        }

        return (string) $result;
    }

    /**
     * @param array<string, string> $data
     */
    private function insertTranslation(Connection $connection, array $data, string $typeId, ?string $languageId): void
    {
        if ($languageId === null) {
            return;
        }

        $data = array_merge($data, [
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'language_id' => $languageId,
            'tax_rule_type_id' => $typeId,
        ]);

        $connection->insert('tax_rule_type_translation', $data);
    }
}
