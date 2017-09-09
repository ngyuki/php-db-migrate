<?php
namespace ngyuki\DbMigrate\Migrate;

use ngyuki\DbMigrate\Adapter\AdapterInterface;

class MigrateContext
{
    private $instances = [];

    public function __construct(Config $config, Logger $logger, AdapterInterface $adapter, $dryRun)
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

    /**
     * @return bool
     */
    public function isDryRun()
    {
        return $this->get('dryRun');
    }
}
