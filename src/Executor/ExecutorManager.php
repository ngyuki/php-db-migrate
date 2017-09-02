<?php
namespace ngyuki\DbMigrate\Executor;

class ExecutorManager
{
    /**
     * @var string
     */
    private $workingDirectory;

    /**
     * @var ExecutorInterface[]
     */
    private $extensions = array();


    public function __construct($workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;
    }

    public function add($extension, ExecutorInterface $executor)
    {
        $this->extensions[$extension] = $executor;
    }

    /**
     * @param string $filename
     */
    public function up($filename)
    {
        $executor = $this->detectExecutor($filename);
        $this->execute(function() use ($executor, $filename) {
            $executor->up($filename);
        });
    }

    /**
     * @param string $filename
     */
    public function down($filename)
    {
        $executor = $this->detectExecutor($filename);
        $this->execute(function() use ($executor, $filename) {
            $executor->down($filename);
        });
    }

    /**
     * @param callable $callback
     * @throws \Exception
     */
    private function execute(callable $callback)
    {
        $workingDirectory = $this->workingDirectory;
        $previousDirectory = null;

        if ($workingDirectory !== null) {
            $previousDirectory = getcwd();

            if (chdir($workingDirectory) == false) {
                throw new \RuntimeException("Unable chdir \"$workingDirectory\".");
            }
        }

        try {
            $callback();

            if ($previousDirectory !== null) {
                chdir($previousDirectory);
            }
        } catch (\Exception $ex) {
            if ($previousDirectory !== null) {
                chdir($previousDirectory);
            }

            throw $ex;
        }
    }

    private function detectExecutor($filename)
    {
        foreach ($this->extensions as $extension => $executor) {
            if (substr($filename, -strlen($extension)) === $extension) {
                return $executor;
            }
        }

        return end($this->extensions);
    }
}
