<?php
namespace ngyuki\DbMigrate\Adapter;

interface AdapterInterface
{
    /**
     * @param string $sql
     */
    public function exec($sql);

    public function createTable();

    public function dropTable();

    /**
     * @return array
     */
    public function fetchAll();

    /**
     * @param $version
     * @param \DateTime|null $apply_at
     */
    public function save($version, \DateTime $apply_at = null);

    /**
     * @param $version
     */
    public function delete($version);
}
