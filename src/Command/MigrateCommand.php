<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('migrate')->setDescription('migration')
            ->addArgument('target', InputArgument::OPTIONAL, 'target version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('target');
        $this->migrator->migrate($target);
    }
}
