<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('status')->setDescription('Show migration status')
            ->addOption('missing', '', InputOption::VALUE_NONE, 'when has missing file then not zero exit code');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $missing = (bool)$input->getOption('missing');
        return $this->locator->reporter->show($missing);
    }
}
