<?php
namespace ngyuki\DbMigrate\Adapter;

use PDO;

class PdoMySqlAdapter implements AdapterInterface
{
    const TABLE_NAME = 'db_migrate';

    /**
     * @var PDO
     */
    protected $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function quotedTable()
    {
        return '`' . self::TABLE_NAME . '`';
    }

    /**
     * @param string $sql
     */
    public function exec($sql)
    {
        $this->pdo->exec($sql);
    }

    public function isExistTable()
    {
        $stmt = $this->pdo->query('show tables');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return in_array(self::TABLE_NAME, $tables);
    }

    public function createTable()
    {
        if ($this->isExistTable()) {
            return;
        }

        $sql = "
            create table {$this->quotedTable()} (
                version varchar (255) not null,
                apply_at datetime not null,
                primary key (version) 
            )
        ";

        $this->pdo->exec($sql);
    }

    public function dropTable()
    {
        if ($this->isExistTable() == false) {
            return;
        }

        $this->pdo->exec("drop table {$this->quotedTable()}");
    }

    public function fetchAll()
    {
        if ($this->isExistTable() == false) {
            return array();
        }

        $sql = "select * from {$this->quotedTable()} order by version";

        $stmt = $this->pdo->query($sql);

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

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("delete from {$this->quotedTable()} where version = ?");
            $stmt->execute(array($version));

            $stmt = $this->pdo->prepare("insert into {$this->quotedTable()} (version, apply_at) values (?, ?)");
            $stmt->execute(array($version, $apply_at->format('Y-m-d\TH:i:s')));

            $this->pdo->commit();
        } catch (\Exception $ex) {
            $this->pdo->rollback();
            throw $ex;
        }
    }

    public function delete($version)
    {
        if ($this->isExistTable() == false) {
            return;
        }

        $stmt = $this->pdo->prepare("delete from {$this->quotedTable()} where version = ?");
        $stmt->execute(array($version));
    }
}