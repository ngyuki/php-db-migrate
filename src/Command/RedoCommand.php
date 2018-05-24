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
        $this->locator->migrator->down();
        $this->locator->migrator->up();
    }
}
