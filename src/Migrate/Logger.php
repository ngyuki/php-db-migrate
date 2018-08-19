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

    public function info($message)
    {
        $this->output->writeln("<info>$message</info>");
    }

    public function verbose($message)
    {
        if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $message = preg_replace('/^/m', "\t", $message);
            $this->output->writeln($message);
        }
    }
}
