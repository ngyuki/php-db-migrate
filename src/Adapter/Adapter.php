<?php
namespace ngyuki\DbMigrate\Adapter;

use ngyuki\DbMigrate\Driver\DriverInterface;
use ngyuki\DbMigrate\Migrate\Logger;

class Adapter implements AdapterInterface
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var bool
     */
    private $dryRun;

    /**
     * @var string
     */
    private $table;

    public function __construct(DriverInterface $driver, Logger $logger, $dryRun, $table)
    {
        $this->driver = $driver;
        $this->logger = $logger;
        $this->dryRun = $dryRun;
        $this->table = $table;
    }

    /**
     * @param string $sql
     * @param array|null $params
     */
    public function exec($sql, array $params = null)
    {
        if ($params === null) {
            $this->logger->verbose($sql);
            if (!$this->dryRun) {
                $this->driver->exec($sql);
            }
        } else {
            $log = $sql . ' ' . json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $this->logger->verbose($log);
            if (!$this->dryRun) {
                $this->driver->exec($sql, $params);
            }
        }
    }

    public function fetchAll()
    {
        if (!$this->driver->existsTable($this->table)) {
            return [];
        }

        $rows = $this->driver->fetchAll($this->table);
        $list = array();

        foreach ($rows as $row) {
            $version = $row['version'];
            $list[$version] = $row;
        }

        ksort($list);
        return $list;
    }

    public function save($version, $content)
    {
        if ($this->dryRun) {
            return;
        }

        if (!$this->driver->existsTable($this->table)) {
            $this->driver->createMigrationTable($this->table);
        }

        $this->driver->transactional(function () use ($version, $content) {
            $this->driver->delete($this->table, [
                'version' => $version,
            ]);
            $this->driver->insert($this->table, [
                'version' => $version,
                'content' => $content,
                'apply_at' => (new \DateTime())->format('Y/m/d H:i:s'),
            ]);
        });
    }

    public function delete($version)
    {
        if ($this->dryRun) {
            return;
        }

        if (!$this->driver->existsTable($this->table)) {
            return;
        }

        $this->driver->transactional(function () use ($version) {
            $this->driver->delete($this->table, [
                'version' => $version,
            ]);
        });
    }

    public function clear()
    {
        $this->driver->clear();
    }
}
