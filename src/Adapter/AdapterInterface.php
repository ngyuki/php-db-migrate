<?php
namespace ngyuki\DbMigrate\Adapter;

interface AdapterInterface
{
    /**
     * @param string $sql
     */
    public function exec($sql);

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getDoctrine();
}
