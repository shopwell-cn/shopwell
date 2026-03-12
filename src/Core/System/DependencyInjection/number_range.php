<?php declare(strict_types=1);
/**
 * @codeCoverageIgnore - DI wiring only
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Adapter\Redis\RedisConnectionProvider;
use Shopwell\Core\System\NumberRange\Aggregate\NumberRangeSalesChannel\NumberRangeSalesChannelDefinition;
use Shopwell\Core\System\NumberRange\Aggregate\NumberRangeState\NumberRangeStateDefinition;
use Shopwell\Core\System\NumberRange\Aggregate\NumberRangeTranslation\NumberRangeTranslationDefinition;
use Shopwell\Core\System\NumberRange\Aggregate\NumberRangeType\NumberRangeTypeDefinition;
use Shopwell\Core\System\NumberRange\Aggregate\NumberRangeTypeTranslation\NumberRangeTypeTranslationDefinition;
use Shopwell\Core\System\NumberRange\Api\NumberRangeController;
use Shopwell\Core\System\NumberRange\Command\MigrateIncrementStorageCommand;
use Shopwell\Core\System\NumberRange\NumberRangeDefinition;
use Shopwell\Core\System\NumberRange\ValueGenerator\NumberRangeValueGenerator;
use Shopwell\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopwell\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\AbstractIncrementStorage;
use Shopwell\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\IncrementRedisStorage;
use Shopwell\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\IncrementSqlStorage;
use Shopwell\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\IncrementStorageRegistry;
use Shopwell\Core\System\NumberRange\ValueGenerator\Pattern\ValueGeneratorPatternDate;
use Shopwell\Core\System\NumberRange\ValueGenerator\Pattern\ValueGeneratorPatternIncrement;
use Shopwell\Core\System\NumberRange\ValueGenerator\Pattern\ValueGeneratorPatternRegistry;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(NumberRangeDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(NumberRangeSalesChannelDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(NumberRangeStateDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(NumberRangeTypeDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(NumberRangeTypeTranslationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(NumberRangeTranslationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(MigrateIncrementStorageCommand::class)
        ->args([service(IncrementStorageRegistry::class)])
        ->tag('console.command');

    $services->set(IncrementSqlStorage::class)
        ->args([service(Connection::class)])
        ->tag('shopwell.value_generator_connector', ['storage' => 'mysql']);

    $services->set(AbstractIncrementStorage::class)
        ->factory([service(IncrementStorageRegistry::class), 'getStorage']);

    $services->set(IncrementRedisStorage::class)
        ->args([
            service('shopwell.number_range.redis'),
            service('lock.factory'),
            service('number_range.repository'),
        ])
        ->tag('shopwell.value_generator_connector', ['storage' => 'redis']);

    $services->set(IncrementStorageRegistry::class)
        ->args([
            tagged_iterator('shopwell.value_generator_connector', indexAttribute: 'storage'),
            '%shopwell.number_range.increment_storage%',
        ]);

    $services->set('shopwell.number_range.redis', 'Redis')
        ->args(['%shopwell.number_range.config.connection%'])
        ->factory([service(RedisConnectionProvider::class), 'getConnection']);

    $services->set(NumberRangeValueGeneratorInterface::class, NumberRangeValueGenerator::class)
        ->public()
        ->args([
            service(ValueGeneratorPatternRegistry::class),
            service('event_dispatcher'),
            service(Connection::class),
        ]);

    $services->set(ValueGeneratorPatternRegistry::class)
        ->args([tagged_iterator('shopwell.value_generator_pattern')]);

    $services->set(ValueGeneratorPatternIncrement::class)
        ->args([service(AbstractIncrementStorage::class)])
        ->tag('shopwell.value_generator_pattern');

    $services->set(ValueGeneratorPatternDate::class)
        ->tag('shopwell.value_generator_pattern');

    $services->set(NumberRangeController::class)
        ->public()
        ->args([service(NumberRangeValueGeneratorInterface::class)])
        ->call('setContainer', [service('service_container')]);
};
