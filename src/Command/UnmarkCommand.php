<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnmarkCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('unmark')->setDescription('Unmark migrated version')
            ->addOption('all', '', InputOption::VALUE_NONE, 'Unmark all versions')
            ->addArgument('version', InputArgument::OPTIONAL, 'Unmark specific version')
        ;

        // Backward Compatibility for v0.2.0
        $this->setAliases(['unset']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all = $input->getOption('all');
        $version = $input->getArgument('version');

        $num = !!$all + !!strlen($version);

        if ($num > 1) {
            throw new \RuntimeException("You can specify that only one --all, version.");
        }

        $migrations = $this->locator->collector->listMigrations();

        if ($all) {
            foreach ($migrations as $version => $migration) {
                if ($migration->isApplied()) {
                    $this->locator->adapter->delete($version);
                    $this->locator->logger->log("unmark: $version");
                }
            }
        } elseif (strlen($version)) {
            if (array_key_exists($version, $migrations) === false) {
                throw new \RuntimeException("version not found: $version");
            }

            $migration = $migrations[$version];

            if ($migration->isApplied() == false) {
                $this->locator->logger->log("version not migrated: $version");
            } else {
                $this->locator->adapter->delete($version);
                $this->locator->logger->log("unmark: $version");
            }
        } else {
            throw new \RuntimeException("Please specify one of --all, version.");
        }
    }
}
