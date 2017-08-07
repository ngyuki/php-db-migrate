<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('set')->setDescription('Set version')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Set all versions')
            ->addArgument('version', InputArgument::OPTIONAL, 'Set specific version')
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
            $this->migrator->setAllVersions();
        } elseif (strlen($version)) {
            $this->migrator->setVersion($version);
        } else {
            throw new \RuntimeException("Please specify one of --all, version.");
        }
    }
}
