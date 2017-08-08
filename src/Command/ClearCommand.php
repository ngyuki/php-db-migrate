<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('clear')->setDescription('clear database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrator->clear();
    }
}
