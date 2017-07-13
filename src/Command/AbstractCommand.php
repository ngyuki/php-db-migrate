<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use ngyuki\DbMigrate\Migrate\ConfigLoader;
use ngyuki\DbMigrate\Migrate\Logger;
use ngyuki\DbMigrate\Migrate\Manager;

abstract class AbstractCommand extends Command
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        // see http://qiita.com/ngyuki/items/d8db4ab6a954c59ed79d
        if ($output->getVerbosity() == $output::VERBOSITY_NORMAL && $input->getOption('verbose')) {
            $output->setVerbosity($output::VERBOSITY_VERBOSE);
        }

        $configPath = $input->getOption('config');
        $dryRun = $input->getOption('dry-run');

        $loader = new ConfigLoader();
        $fn = $loader->resolve($configPath);

        $config = $loader->load($fn);
        $config->dryRun = $dryRun;

        $logger = new Logger($output);

        $this->manager = new Manager($config, $logger);
    }
}
