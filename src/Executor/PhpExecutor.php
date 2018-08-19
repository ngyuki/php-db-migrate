<?php
namespace ngyuki\DbMigrate\Executor;

use ngyuki\DbMigrate\Migrate\MigrationContext;
use ReflectionFunction;
use Symfony\Component\Console\Output\OutputInterface;

class PhpExecutor implements ExecutorInterface
{
    /**
     * @var MigrationContext
     */
    private $context;

    /**
     * @var array
     */
    private $params;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(MigrationContext $context, OutputInterface $output)
    {
        $this->context = $context;
        $this->output = $output;

        $this->params = $context->getConfig() + [
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
            // @phan-suppress-next-line PhanSuspiciousBinaryAddLists
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

        $ret = $this->outputLineHandle(function () use ($func, $args) {
            return $func->invokeArgs($args);
        }, function ($line) {
            if ($this->output->isVerbose()) {
                $this->output->writeln("\t" . $line, $this->output::OUTPUT_RAW);
            }
        });

        if ($ret !== null) {
            $ret = (array)$ret;
            foreach ($ret as $sql) {
                if (!is_array($sql)) {
                    $this->context->exec($sql);
                } else {
                    list ($sql, $params) = $sql + [null, null];
                    $this->context->exec($sql, $params);
                }
            }
        }
    }

    private function outputLineHandle(callable $process, callable $handler)
    {
        $buffer = '';

        ob_start(function ($output) use (&$buffer, $handler) {
            $buffer .= $output;
            for (;;) {
                $pos = strpos($buffer, "\n");
                if ($pos === false) {
                    break;
                }
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);
                $handler($line);
            }
        }, 1);

        try {
            return $process();
        } finally {
            ob_end_flush();
            if (strlen($buffer)) {
                $handler(rtrim($buffer, "\n"));
            }
        }
    }
}
