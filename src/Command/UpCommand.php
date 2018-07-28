<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('up')->setDescription('up one version')
            ->addOption('all', '', InputOption::VALUE_NONE, 'up all versions');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all = $input->getOption('all');
        $migrations = $this->locator->collector->listStatuses();

        $up = [];

        foreach ($migrations as $version => $migration) {
            if ($migration->isApplied() == false) {
                $up[$version] = $migration;
                if (!$all) {
                    break;
                }
            }
        }

        $this->locator->migrator->doMigrate($migrations, $up, []);
    }
}
