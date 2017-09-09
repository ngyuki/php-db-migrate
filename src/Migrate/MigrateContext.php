<?php
namespace ngyuki\DbMigrate\Migrate;

use ngyuki\DbMigrate\Adapter\AdapterFactory;
use ngyuki\DbMigrate\Adapter\AdapterInterface;
use ngyuki\DbMigrate\Executor\ExecutorManager;
use ngyuki\DbMigrate\Executor\PhpExecutor;
use ngyuki\DbMigrate\Executor\SqlExecutor;

class MigrateContext
{
    private $instances = [];

    public function __construct(Config $config, Logger $logger, AdapterInterface $adapter)
    {
        $this->instances = get_defined_vars();
    }

    public function get($id)
    {
        return $this->instances[$id];
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->get(lcfirst(substr(__FUNCTION__, 3)));
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->get(lcfirst(substr(__FUNCTION__, 3)));
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->get(lcfirst(substr(__FUNCTION__, 3)));
    }
}
