<?php
namespace ngyuki\DbMigrate\Executor;

use ngyuki\DbMigrate\Adapter\AdapterInterface;

class SqlExecutor implements ExecutorInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function up($content)
    {
        list ($sql) = $this->read($content);
        $this->execute($sql);
    }

    public function down($content)
    {
        list (, $sql) = $this->read($content);
        $this->execute($sql);
    }

    private function read($content)
    {
        list ($up, $down) = preg_split('/^.*\{\{ *down *\}\}.*$/m', $content, 2) + array(null, null);
        return array($up, $down);
    }

    private function execute($sql, $delimiter = ';')
    {
        $sql = $this->stripComment($sql);
        $list = explode($delimiter, $sql);

        foreach ($list as $sql) {
            $sql = trim($sql);
            if (strlen($sql) > 0) {
                $this->adapter->exec($sql);
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
