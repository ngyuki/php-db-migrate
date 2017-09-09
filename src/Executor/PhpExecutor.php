<?php
namespace ngyuki\DbMigrate\Executor;

use ngyuki\DbMigrate\Migrate\MigrateContext;

class PhpExecutor implements ExecutorInterface
{   /**
     * @var MigrateContext
     */
    private $context;

    public function __construct(MigrateContext $context)
    {
        $this->context = $context;
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
        /** @noinspection PhpIncludeInspection */
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
        call_user_func($func, $this->context);
    }
}
