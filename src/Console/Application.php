<?php
namespace ngyuki\DbMigrate\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;

use ngyuki\DbMigrate\Command;

class Application extends BaseApplication
{
    const NAME = 'db-migrate';
    const VERSION = '@dev';

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\MigrateCommand();
        $commands[] = new Command\StatusCommand();
        $commands[] = new Command\FixCommand();
        $commands[] = new Command\ExecCommand();

        return $commands;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->setOptions(array(
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'),
            new InputOption('--verbose', '-v', InputOption::VALUE_NONE, 'Increase the verbosity of messages.'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version.'),
            new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output.'),
            new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output.'),
        ));

        $definition->addOptions(array(
            new InputOption('--config', '-c', InputOption::VALUE_OPTIONAL, "Config script filename."),
            new InputOption('--dry-run', '-n', InputOption::VALUE_NONE, "Execute dry run mode."),
        ));

        return $definition;
    }
}
