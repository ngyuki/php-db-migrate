<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('migrate')->setDescription('Execute migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->manager->migrate();
    }
}
