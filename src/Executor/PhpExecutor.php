<?php
namespace ngyuki\DbMigrate\Executor;

use ngyuki\DbMigrate\Migrate\Logger;

class PhpExecutor implements ExecutorInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $args;

    /**
     * @var bool
     */
    private $dryRun;

    public function __construct(Logger $logger, $args, $dryRun)
    {
        $this->logger = $logger;
        $this->args = $args;
        $this->dryRun = $dryRun;
    }

    public function up($content)
    {
        list ($func, ) = $this->validate($this->read($content));
        $this->execute($func);
    }

    public function down($content)
    {
        list (, $func) = $this->validate($this->read($content));
        $this->execute($func);
    }

    private function read($content)
    {
        $tmp = tmpfile();
        $file = stream_get_meta_data($tmp)['uri'];
        file_put_contents($file, $content);
        $arr = include $file;
        unset($tmp);

        if ($arr instanceof \Closure) {
            return array($arr, null);
        }

        if (is_array($arr)) {
            $arr = array_values($arr) + array(null, null);
            return $arr;
        }

        return array(null, null);
    }

    private function validate($arr)
    {
        list ($up, $down) = $arr;

        if ($up instanceof \Closure) {
            if ($down === null) {
                return $arr;
            }
            if ($down instanceof \Closure) {
                return $arr;
            }
        }

        throw new \RuntimeException("Invalid php script ... should be return array(Closure, Closure)");
    }

    private function execute(\Closure $func)
    {
        $this->logger->verbose("execute php script...");

        if ($this->dryRun == false) {
            call_user_func_array($func, $this->args);
        }
    }
}
