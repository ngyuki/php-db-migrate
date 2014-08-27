<?php
namespace TestHelper;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;

class ApplicationTester
{
    private $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function run()
    {
        $argv = func_get_args();
        array_unshift($argv, __FILE__);

        $input = new ArgvInput($argv);
        $output = new NullOutput();

        return $this->application->run($input, $output);
    }

    public function runArgs($argv)
    {
        array_unshift($argv, __FILE__);

        $input = new ArgvInput($argv);
        $output = new NullOutput();

        return $this->application->run($input, $output);
    }
}
