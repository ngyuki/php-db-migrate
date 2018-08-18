<?php
namespace ngyuki\DbMigrate\Executor;

use ngyuki\DbMigrate\Migrate\MigrationContext;
use ReflectionFunction;

class PhpExecutor implements ExecutorInterface
{   /**
     * @var array
     */
    private $params;

    public function __construct(MigrationContext $context)
    {
        $this->params = $context->config + [
            MigrationContext::class => $context,
        ];
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
        $func = new ReflectionFunction($func);
        $params = $func->getParameters();
        $args = [];
        foreach ($params as $param) {
            $class = $param->getClass();
            if ($class) {
                $name = $class->getName();
                if (array_key_exists($name, $this->params)) {
                    $args[] = $this->params[$name];
                    continue;
                }
            }
            $name = $param->getName();
            if (array_key_exists($name, $this->params)) {
                $args[] = $this->params[$name];
                continue;
            }
            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }
            if ($param->isOptional()) {
                break;
            }
            throw new \RuntimeException("Unable resolve argument '$name'");
        }
        $func->invokeArgs($args);
    }
}
