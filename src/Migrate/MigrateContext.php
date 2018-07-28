<?php
namespace ngyuki\DbMigrate\Migrate;

use ngyuki\DbMigrate\Adapter\AdapterInterface;

/**
 * @property Logger $logger
 * @property bool $dryRun
 */
class MigrateContext implements \ArrayAccess
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var array
     */
    private $properties;

    public function __construct(array $config, Logger $logger, AdapterInterface $adapter, $dryRun)
    {
        $this->config = $config;
        $this->adapter = $adapter;

        $this->properties = [
            'logger' => $logger,
            'dryRun' => $dryRun,
        ];
    }

    public function exec($sql)
    {
        $this->adapter->exec($sql);
    }

    public function __get($name)
    {
        return $this->properties[$name];
    }

    /**
     * @param $id
     * @return mixed
     * @deprecated
     */
    public function get($id)
    {
        return $this->config[$id];
    }

    /**
     * @return array
     * @deprecated
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Logger
     * @deprecated
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return AdapterInterface
     * @deprecated
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isDryRun()
    {
        return $this->dryRun;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->config);
    }

    public function offsetGet($offset)
    {
        return $this->config[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException(__METHOD__);
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException(__METHOD__);
    }
}
