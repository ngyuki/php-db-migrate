<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('up')->setDescription('up one version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->manager->up();
    }
}
