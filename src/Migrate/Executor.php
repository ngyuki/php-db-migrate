<?php
namespace ngyuki\DbMigrate\Migrate;

use ngyuki\DbMigrate\Adapter\AdapterInterface;

class Executor
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var array
     */
    private $extract;

    /**
     * @var string
     */
    private $workingDirectory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var boolean
     */
    private $dryRun = false;

    /**
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(Config $config, Logger $logger)
    {
        $this->adapter = $config->adapter;
        $this->extract = $config->extract;
        $this->dryRun = $config->dryRun;
        $this->workingDirectory = $config->workingDirectory;

        $this->logger = $logger;
    }

    /**
     * @param string $fn
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function execute($fn)
    {
        $fn = realpath($fn);

        $extensions = array(
            '.sql.php' => 'executeSqlPhp',
            '.php.sql' => 'executeSqlPhp',
            '.php'     => 'executePhp',
            '.sql'     => 'executeSql',
        );

        $detectedMethod = null;

        foreach ($extensions as $ext => $method) {
            if (substr($fn, -strlen($ext)) === $ext) {
                $detectedMethod = $method;
                break;
            }
        }

        if ($detectedMethod === null) {
            $detectedMethod = end($extensions);
        }

        $workingDirectory = $this->workingDirectory;
        $previousDirectory = null;

        if ($workingDirectory !== null) {
            $previousDirectory = getcwd();

            if (chdir($workingDirectory) == false) {
                throw new \RuntimeException("Unable chdir \"$workingDirectory\".");
            }
        }

        try {
            $this->$detectedMethod($fn);

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
     * @param string $file
     * @return string
     */
    private function stripComment($file)
    {
        $file = preg_replace_callback('/\/\*(.*?)\*\//s', function () {
            return '';
        }, $file);

        return $file;
    }

    /**
     * @param string $fn
     */
    protected function executeSql($fn)
    {
        $file = file_get_contents($fn);
        $this->executeSqlRaw($file, ';');
    }

    /**
     * @param string $fn
     */
    protected function executeSqlPhp($fn)
    {
        $template = new Template();
        $file = $template->render($fn, $this->extract);
        $this->executeSqlRaw($file, $template->delimiter());
    }

    private function executeSqlRaw($sql, $delimiter)
    {
        $sql = $this->stripComment($sql);
        $list = explode($delimiter, $sql);

        foreach ($list as $sql) {
            $sql = trim($sql);

            if (strlen($sql) > 0) {
                $this->logger->verbose($sql . $delimiter);

                if ($this->dryRun == false) {
                    $this->adapter->exec($sql);
                }
            }
        }
    }

    /**
     * @param string $fn
     */
    protected function executePhp($fn)
    {
        if ($this->dryRun == false) {
            extract($this->extract, EXTR_SKIP);

            /* @noinspection PhpIncludeInspection */
            require func_get_arg(0);
        } else {
            $this->logger->verbose("This script is PHP.");
        }
    }
}
