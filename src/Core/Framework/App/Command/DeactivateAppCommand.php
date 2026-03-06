<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\Command;

use Shopwell\Core\Framework\App\AppCollection;
use Shopwell\Core\Framework\App\AppStateService;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * @internal only for use by the app-system
 */
#[AsCommand(
    name: 'app:deactivate',
    description: 'Deactivates an app',
)]
#[Package('framework')]
class DeactivateAppCommand extends AbstractAppActivationCommand
{
    private const ACTION = 'deactivate';

    /**
     * @param EntityRepository<AppCollection> $appRepo
     */
    public function __construct(
        EntityRepository $appRepo,
        private readonly AppStateService $appStateService
    ) {
        parent::__construct($appRepo, self::ACTION);
    }

    public function runAction(string $appId, Context $context): void
    {
        $this->appStateService->deactivateApp($appId, $context);
    }
}
