<?php
namespace ngyuki\DbMigrate\Migrate;

use ngyuki\DbMigrate\Adapter\AdapterInterface;

class MigrationContextImpl implements MigrationContext
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
     * @var bool
     */
    private $dryRun;

    public function __construct(array $config, bool $dryRun, Logger $logger, AdapterInterface $adapter)
    {
        $this->config = $config;
        $this->adapter = $adapter;
        $this->logger = $logger;
        $this->dryRun = $dryRun;
    }

    public function exec($sql, array $params = null)
    {
        $this->adapter->exec($sql, $params);
    }

    public function info($message)
    {
        $this->logger->info($message);
    }

    public function verbose($message)
    {
        $this->logger->verbose($message);
    }

    public function isDryRun()
    {
        return $this->dryRun;
    }

    public function getConfig()
    {
        return $this->config;
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