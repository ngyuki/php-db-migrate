<?php
namespace ngyuki\DbMigrate\Adapter;

use Doctrine\DBAL\DriverManager;

class PdoAdapter implements AdapterInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $doctrine;

    public function __construct(\PDO $pdo)
    {
        $this->doctrine = DriverManager::getConnection(array(
            'pdo' => $pdo,
        ));
    }

    /**
     * @param string $sql
     */
    public function exec($sql)
    {
        $this->doctrine->exec($sql);
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }
}
