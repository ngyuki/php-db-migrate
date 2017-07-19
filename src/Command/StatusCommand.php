<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('status')->setDescription('Show migrate status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->manager->showStatus();
    }
}
