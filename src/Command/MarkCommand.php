<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MarkCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('mark')->setDescription('Mark migrated version')
            ->addOption('all', '', InputOption::VALUE_NONE, 'Mark all versions')
            ->addArgument('version', InputArgument::OPTIONAL, 'Mark specific version')
        ;

        // Backward Compatibility for v0.2.0
        $this->setAliases(['set']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all = $input->getOption('all');
        $version = $input->getArgument('version');

        $num = !!$all + !!strlen($version);

        if ($num > 1) {
            throw new \RuntimeException("You can specify that only one --all, version.");
        }

        $migrations = $this->locator->collector->listStatuses();

        if ($all) {
            foreach ($migrations as $version => $migration) {
                if ($migration->isApplied()) {
                    // skip
                } elseif ($migration->isMissing()) {
                    // skip
                } else {
                    $this->locator->adapter->save($version, $migration->getContent());
                    $this->locator->logger->log("mark version: $version");
                }
            }
        } elseif (strlen($version)) {
            if (array_key_exists($version, $migrations) === false) {
                throw new \RuntimeException("version not found: $version");
            }

            $migration = $migrations[$version];

            if ($migration->isApplied()) {
                $this->locator->logger->log("version already migrated: $version");
            } elseif ($migration->isMissing()) {
                $this->locator->logger->log("mark version: $version is missing");
            } else {
                $this->locator->adapter->save($version, $migration->getContent());
                $this->locator->logger->log("mark version: $version");
            }
        } else {
            throw new \RuntimeException("Please specify one of --all, version.");
        }
    }
}
