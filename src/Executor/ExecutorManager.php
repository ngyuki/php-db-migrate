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
     * @param string $content
     */
    public function up($filename, $content)
    {
        $executor = $this->getExecutor($filename);
        $this->execute(function () use ($executor, $content) {
            $executor->up($content);
        });
    }

    /**
     * @param string $filename
     * @param string $content
     */
    public function down($filename, $content)
    {
        $executor = $this->getExecutor($filename);
        $this->execute(function () use ($executor, $content) {
            $executor->down($content);
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
                throw new \RuntimeException("Unable change directory \"$workingDirectory\".");
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

    /**
     * @param $filename
     * @return ExecutorInterface
     */
    private function getExecutor($filename)
    {
        foreach ($this->extensions as $extension => $executor) {
            if (substr($filename, -strlen($extension)) === $extension) {
                return $executor;
            }
        }

        $executor = end($this->extensions);
        return $executor;
    }
}
