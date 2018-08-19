<?php
namespace ngyuki\DbMigrate\Adapter;

interface AdapterInterface
{
    /**
     * @param string $sql
     * @param array|null $params
     */
    public function exec($sql, array $params = null);

    /**
     * @return array
     */
    public function fetchAll();

    /**
     * @param string $version
     * @param string $content
     * @return void
     */
    public function save($version, $content);

    /**
     * @param $version
     */
    public function delete($version);

    public function clear();
}
