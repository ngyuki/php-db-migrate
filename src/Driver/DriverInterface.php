<?php
namespace ngyuki\DbMigrate\Driver;

interface DriverInterface
{
    public function exec($sql, array $params = null);

    public function existsTable($table);

    public function createMigrationTable($table);

    public function fetchAll($table);

    public function insert($table, array $data);

    public function delete($table, array $wheres);

    public function transactional(callable $callback);

    public function clear();
}
