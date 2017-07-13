<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('fix')->setDescription('Fix migrate version')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Fix all versions')
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Clear version')
            ->addArgument('version', InputArgument::OPTIONAL, 'Fix version')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all = $input->getOption('all');
        $clear = $input->getOption('clear');
        $version = $input->getArgument('version');

        $num = !!$all + !!$clear + !!strlen($version);

        if ($num > 1) {
            throw new \RuntimeException("You can specify that only one --all, --clear, version.");
        }

        if ($clear) {
            $this->manager->clearVersion();
        } elseif ($all) {
            $this->manager->fixAllVersions();
        } elseif (strlen($version)) {
            $this->manager->fixVersion($version);
        } else {
            throw new \RuntimeException("Please specify one of --all, --clear, version.");
        }
    }
}
