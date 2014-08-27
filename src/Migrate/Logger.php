<?php
namespace ngyuki\DbMigrate\Migrate;

use Symfony\Component\Console\Output\OutputInterface;

class Logger
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function log($log)
    {
        $this->output->writeln("<info>$log</info>");
    }

    public function verbose($log)
    {
        if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL)
        {
            $log = preg_replace('/^/m', "\t", $log);
            $this->output->writeln($log);
        }
    }
}
