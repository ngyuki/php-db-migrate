<?php
namespace ngyuki\DbMigrate\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

use ngyuki\DbMigrate\Command;

class Application extends BaseApplication
{
    /**
     * @var string
     */
    private $longVersion;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null, $version = null)
    {
        parent::__construct($name, $version);

        $commands = array();
        $commands[] = new Command\MigrateCommand();
        $commands[] = new Command\UpCommand();
        $commands[] = new Command\DownCommand();
        $commands[] = new Command\RedoCommand();
        $commands[] = new Command\StatusCommand();
        $commands[] = new Command\MarkCommand();
        $commands[] = new Command\UnmarkCommand();
        $commands[] = new Command\ExecCommand();
        $commands[] = new Command\ClearCommand();

        $this->addCommands($commands);
    }

    public function setLongVersion($longVersion)
    {
        $this->longVersion = $longVersion;
        return $this;
    }

    public function getLongVersion()
    {
        return $this->longVersion;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition()
    {
        // Remove --no-interaction|-n
        return new InputDefinition(array(
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),

            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'),
            new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output'),
            new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output'),
        ));
    }
}
