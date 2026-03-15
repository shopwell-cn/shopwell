<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\Command;

use Shopwell\Core\Framework\Adapter\Console\ShopwellStyle;
use Shopwell\Core\Framework\App\ShopIdChangeResolver\Resolver;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(
    name: 'app:shop-id:change',
    description: 'Change the shop ID by choosing a resolution strategy',
)]
#[Package('framework')]
class ChangeShopIdCommand extends Command
{
    public function __construct(private readonly Resolver $shopIdChangeResolver)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('strategy', InputArgument::OPTIONAL, 'The strategy that should be applied');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwellStyle($input, $output);

        $availableStrategies = $this->shopIdChangeResolver->getAvailableStrategies();
        $strategy = $input->getArgument('strategy');

        if ($strategy === null || !\array_key_exists($strategy, $availableStrategies)) {
            if ($strategy !== null) {
                $io->note(\sprintf('Strategy with name: "%s" not found.', $strategy));
            }

            $strategy = $io->choice(
                'Choose what strategy should be applied when changing the shop ID?',
                $availableStrategies
            );
        }

        $this->shopIdChangeResolver->resolve($strategy, Context::createCLIContext());

        $io->success('Strategy "' . $strategy . '" was applied successfully');

        return self::SUCCESS;
    }
}
