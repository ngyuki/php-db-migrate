<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('down')->setDescription('down one version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrations = $this->locator->collector->listStatuses();

        $down = array();

        foreach ($migrations as $version => $migration) {
            if ($migration->isApplied()) {
                $down = [];
                $down[$version] = $migration;
            }
        }

        $this->locator->migrator->doMigrate($migrations, [], $down);
    }
}
