<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RedoCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('redo')->setDescription('down/up one version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrations = $this->locator->collector->listMigrations();

        $redo = [];

        foreach ($migrations as $version => $migration) {
            if ($migration->isApplied()) {
                $redo = [$version => $migration];
            }
        }

        $up = [];
        $down = [];

        if ($redo) {
            $down = $redo;
            if (!reset($redo)->isMissing()) {
                $up = $redo;
            }
        }

        $this->locator->migrator->doMigrate($migrations, $up, $down);
    }
}
