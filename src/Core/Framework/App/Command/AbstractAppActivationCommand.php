<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\Command;

use Shopwell\Core\Framework\Adapter\Console\ShopwellStyle;
use Shopwell\Core\Framework\App\AppCollection;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
abstract class AbstractAppActivationCommand extends Command
{
    /**
     * @param EntityRepository<AppCollection> $appRepo
     */
    public function __construct(
        protected EntityRepository $appRepo,
        private readonly string $action,
    ) {
        parent::__construct();
    }

    abstract public function runAction(string $appId, Context $context): void;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwellStyle($input, $output);
        $context = Context::createCLIContext();

        $appName = $input->getArgument('name');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $appName));

        $id = $this->appRepo->searchIds($criteria, $context)->firstId();

        if (!$id) {
            $io->error("No app found for \"{$appName}\".");

            return self::FAILURE;
        }

        $this->runAction($id, $context);

        $io->success(\sprintf('App %sd successfully.', $this->action));

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the app, has also to be the name of the folder under
                which the app can be found under custom/apps');
    }
}
