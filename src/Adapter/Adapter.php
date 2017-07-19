<?php
namespace ngyuki\DbMigrate\Adapter;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

class Adapter implements AdapterInterface
{
    const TABLE_NAME = 'db_migrate';

    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(\PDO $pdo)
    {
        $this->connection = DriverManager::getConnection(array(
            'pdo' => $pdo,
        ));
    }

    /**
     * @param string $sql
     */
    public function exec($sql)
    {
        $this->connection->exec($sql);
    }

    public function isExistTable()
    {
        return $this->connection->getSchemaManager()->tablesExist(array(self::TABLE_NAME));
    }

    public function createTable()
    {
        if ($this->isExistTable()) {
            return;
        }

        $table = new Table(self::TABLE_NAME);

        $table->addColumn('version', Type::STRING, array(
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ));

        $table->addColumn('apply_at', Type::DATETIME, array(
            'notnull' => false,
            'default' => null,
        ));

        $table->setPrimaryKey(array('version'));

        $this->connection->getSchemaManager()->createTable($table);
    }

    public function dropTable()
    {
        if ($this->isExistTable()) {
            $this->connection->getSchemaManager()->dropTable(self::TABLE_NAME);
        }
    }

    public function fetchAll()
    {
        if ($this->isExistTable() == false) {
            return array();
        }

        $stmt = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_NAME, 'M')
            ->orderBy('version')
            ->execute()
        ;

        $list = array();

        foreach ($stmt as $row) {
            $version = $row['version'];
            $list[$version] = $row;
        }

        ksort($list);
        return $list;
    }

    public function save($version, \DateTime $apply_at = null)
    {
        if ($apply_at === null) {
            $apply_at = new \DateTime();
        }

        $this->createTable();

        $this->connection->beginTransaction();
        try {
            $this->connection->delete(self::TABLE_NAME, array(
                'version' => $version,
            ));
            $this->connection->insert(self::TABLE_NAME, array(
                'version' => $version,
                'apply_at' => $apply_at->format('Y-m-d\TH:i:s'),
            ));
            $this->connection->commit();
        } catch (\Exception $ex) {
            $this->connection->rollback();
            throw $ex;
        }
    }

    public function delete($version)
    {
        if ($this->isExistTable() == false) {
            return;
        }

        $this->connection->delete(self::TABLE_NAME, array(
            'version' => $version,
        ));
    }
}
