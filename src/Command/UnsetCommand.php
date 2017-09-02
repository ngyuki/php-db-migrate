<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnsetCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('unset')->setDescription('Unset version')
            ->addOption('all', '', InputOption::VALUE_NONE, 'Unset all versions')
            ->addArgument('version', InputArgument::OPTIONAL, 'Unset specific version')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all = $input->getOption('all');
        $version = $input->getArgument('version');

        $num = !!$all + !!strlen($version);

        if ($num > 1) {
            throw new \RuntimeException("You can specify that only one --all, version.");
        }

        if ($all) {
            $this->migrator->unsetAllVersions();
        } elseif (strlen($version)) {
            $this->migrator->unsetVersion($version);
        } else {
            throw new \RuntimeException("Please specify one of --all, version.");
        }
    }
}
