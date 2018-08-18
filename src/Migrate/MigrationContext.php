<?php
namespace ngyuki\DbMigrate\Migrate;

use ngyuki\DbMigrate\Adapter\AdapterInterface;

/**
 * @property array $config
 * @property bool $dryRun
 */
class MigrationContext implements \ArrayAccess
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
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $properties;

    public function __construct(array $config, Logger $logger, AdapterInterface $adapter, $dryRun)
    {
        $this->adapter = $adapter;
        $this->logger = $logger;

        $this->properties = [
            'config' => $config,
            'dryRun' => $dryRun,
        ];
    }

    public function exec($sql)
    {
        $this->adapter->exec($sql);
    }

    public function log($log)
    {
        $this->logger->log($log);
    }

    public function verbose($log)
    {
        $this->logger->verbose($log);
    }

    public function __get($name)
    {
        return $this->properties[$name];
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
