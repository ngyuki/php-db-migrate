<?php
namespace ngyuki\DbMigrate\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use ngyuki\DbMigrate\Migrate\ConfigLoader;
use ngyuki\DbMigrate\Migrate\Logger;
use ngyuki\DbMigrate\Migrate\Migrator;

abstract class AbstractCommand extends Command
{
    /**
     * @var Migrator
     */
    protected $migrator;

    protected function configure()
    {
        parent::configure();

        $this->getDefinition()->addOptions(array(
            new InputOption('--config', '-c', InputOption::VALUE_OPTIONAL, "Config script filename."),
            new InputOption('--dry-run', '-n', InputOption::VALUE_NONE, "Execute dry run mode."),
        ));
    }

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

        if (strlen($configPath) === 0) {
            $configPath = getenv('PHP_DB_MIGRATE_CONFIG');
        }

        $loader = new ConfigLoader();
        $fn = $loader->resolve($configPath);

        $config = $loader->load($fn);
        $config->dryRun = $dryRun;

        $this->migrator = Migrator::create(new Logger($output), $config);
    }}
