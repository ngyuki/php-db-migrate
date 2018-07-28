<?php
namespace ngyuki\DbMigrate\Command;

use ngyuki\DbMigrate\Migrate\MigrationFilter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('down')->setDescription('down one version')
            ->addOption('all', '', InputOption::VALUE_NONE, 'down all versions')
            ->addOption('missing', '', InputOption::VALUE_NONE, 'down missing versions');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all = $input->getOption('all');
        $missing = $input->getOption('missing');
        if ($all && $missing) {
            throw new \RuntimeException("You can specify that only one --all, --missing.");
        }

        $migrations = $this->locator->collector->listStatuses();
        $down = (new MigrationFilter())->down($migrations, $missing, $all);
        $this->locator->migrator->doMigrate($migrations, [], $down);
    }
}
