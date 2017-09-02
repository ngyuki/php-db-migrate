<?php
namespace ngyuki\DbMigrate\Executor;

use ngyuki\DbMigrate\Adapter\AdapterInterface;
use ngyuki\DbMigrate\Migrate\Logger;

class SqlExecutor implements ExecutorInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var bool
     */
    private $dryRun;

    public function __construct(Logger $logger, AdapterInterface $adapter, $dryRun)
    {
        $this->logger = $logger;
        $this->adapter = $adapter;
        $this->dryRun = $dryRun;
    }

    public function up($filename)
    {
        list ($sql) = $this->read($filename);
        $this->execute($sql);
    }

    public function down($filename)
    {
        list (, $sql) = $this->read($filename);
        $this->execute($sql);
    }

    private function read($filename)
    {
        $file = file_get_contents($filename);
        list ($up, $down) = preg_split('/^.*\{\{ *down *\}\}.*$/m', $file, 2) + array(null, null);
        return array($up, $down);
    }

    private function execute($sql, $delimiter = ';')
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
}
