<?php
namespace ngyuki\DbMigrate\Driver;

use PDO;

class PdoMySqlDriver implements DriverInterface
{
    /**
     * @var PDO
     */
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function quoteIdentity($name)
    {
        return '`' . strtr($name, '`', '``') . '`';
    }

    /**
     * @param string $sql
     * @param array|null $params
     */
    public function exec($sql, array $params = null)
    {
        if ($params === null) {
            $this->pdo->exec($sql);
        } else {
            $this->pdo->prepare($sql)->execute($params);
        }
    }

    public function existsTable($table)
    {
        $stmt = $this->pdo->query('show tables');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return in_array($table, $tables);
    }

    public function createMigrationTable($table)
    {
        $this->exec("
            create table {$this->quoteIdentity($table)} (
                version varchar (255) not null,
                apply_at datetime not null,
                content LONGBLOB,
                primary key (version)
            )
        ");
    }

    public function fetchAll($table)
    {
        return $this->pdo->query("select * from {$this->quoteIdentity($table)}")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($table, array $data)
    {
        $columns = [];
        $params = [];

        foreach ($data as $name => $value) {
            $columns[] = $this->quoteIdentity($name);
            $params[] = $value;
        }

        $holders = implode(',', array_fill_keys(array_keys($columns), '?'));
        $columns = implode(',', $columns);

        $sql = "insert into {$this->quoteIdentity($table)} ($columns) values ($holders)";
        $this->pdo->prepare($sql)->execute($params);
    }

    public function delete($table, array $wheres)
    {
        $columns = [];
        $params = [];

        foreach ($wheres as $name => $value) {
            $columns[] = "{$this->quoteIdentity($name)} = ?";
            $params[] = $value;
        }

        $columns = implode(',', $columns);

        $sql = "delete from {$this->quoteIdentity($table)} where $columns";
        $this->pdo->prepare($sql)->execute($params);
    }

    public function clear()
    {
        $database = $this->pdo->query('select database()')->fetchColumn();
        $quotedDatabase = $this->quoteIdentity($database);

        $this->exec("drop database if exists $quotedDatabase");
        $this->exec("create database $quotedDatabase");
        $this->exec("use $quotedDatabase");
    }

    public function transactional(callable $callback)
    {
        $this->pdo->beginTransaction();
        try {
            $ret = $callback();
            $this->pdo->commit();
            return $ret;
        } catch (\Exception $ex) {
            $this->pdo->rollback();
            throw $ex;
        }
    }
}
