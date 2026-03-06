<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\MessageQueue\ScheduledTask\MessageQueue;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\MessageQueue\ScheduledTask\Registry\TaskRegistry;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @final
 *
 * @internal
 */
#[AsMessageHandler]
#[Package('framework')]
class RegisterScheduledTaskHandler
{
    /**
     * @internal
     */
    public function __construct(private readonly TaskRegistry $registry)
    {
    }

    public function __invoke(RegisterScheduledTaskMessage $message): void
    {
        $this->registry->registerTasks();
    }
}
