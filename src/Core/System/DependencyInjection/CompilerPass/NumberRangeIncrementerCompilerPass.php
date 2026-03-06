<?php declare(strict_types=1);

namespace Shopwell\Core\System\DependencyInjection\CompilerPass;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\DependencyInjection\DependencyInjectionException;
use Shopwell\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\IncrementRedisStorage;
use Shopwell\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\IncrementSqlStorage;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[Package('framework')]
class NumberRangeIncrementerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $storage = $container->getParameter('shopwell.number_range.increment_storage');

        switch ($storage) {
            case 'mysql':
                $container->removeDefinition('shopwell.number_range.redis');
                $container->removeDefinition(IncrementRedisStorage::class);
                break;
            case 'redis':
                if ($container->getParameter('shopwell.number_range.config.connection') === null) {
                    throw DependencyInjectionException::redisNotConfiguredForNumberRangeIncrementer();
                }

                $container->removeDefinition(IncrementSqlStorage::class);
                break;
        }
    }
}
