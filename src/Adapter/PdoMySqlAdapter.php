<?php
namespace ngyuki\DbMigrate\Adapter;

use ngyuki\DbMigrate\Migrate\Logger;
use PDO;

class PdoMySqlAdapter implements AdapterInterface
{
    const TABLE_NAME = 'db_migrate_v3';

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var bool
     */
    private $dryRun;

    public function __construct(\PDO $pdo, Logger $logger, $dryRun)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
        $this->dryRun = $dryRun;
    }

    private function quoteIdentity($name)
    {
        return '`' . strtr($name, '`', '``') . '`';
    }

    private function quotedTable()
    {
        return $this->quoteIdentity(self::TABLE_NAME);
    }

    /**
     * @param string $sql
     */
    public function exec($sql)
    {
        $this->logger->verbose($sql);
        if (!$this->dryRun) {
            $this->pdo->exec($sql);
        }
    }

    public function createTable()
    {
        $stmt = $this->pdo->query('show tables');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (in_array(self::TABLE_NAME, $tables)) {
            return;
        }

        $this->logger->log(sprintf("create table %s", self::TABLE_NAME));

        $sql = "
            create table {$this->quotedTable()} (
                version varchar (255) not null,
                apply_at datetime not null,
                content LONGBLOB,
                primary key (version) 
            )
        ";

        $this->pdo->exec($sql);

        if (in_array('db_migrate', $tables)) {
            $this->logger->log(sprintf("insert into %s from %s", self::TABLE_NAME, 'db_migrate'));

            $this->pdo->query("
                insert into {$this->quotedTable()} (version, apply_at) select version, apply_at from db_migrate
            ");
            $this->pdo->query("
                drop table db_migrate
            ");
        }

        $this->logger->log('');
    }

    public function fetchAll()
    {
        $this->createTable();

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

    public function save($version, $content)
    {
        if ($this->dryRun) {
            return;
        }

        $this->createTable();

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("delete from {$this->quotedTable()} where version = ?");
            $stmt->execute(array($version));

            $stmt = $this->pdo->prepare("insert into {$this->quotedTable()} (version, content, apply_at) values (?, ?, now())");
            $stmt->execute(array($version, $content));

            $this->pdo->commit();
        } catch (\Exception $ex) {
            $this->pdo->rollback();
            throw $ex;
        }
    }

    public function delete($version)
    {
        if ($this->dryRun) {
            return;
        }

        $this->createTable();

        $stmt = $this->pdo->prepare("delete from {$this->quotedTable()} where version = ?");
        $stmt->execute(array($version));
    }

    public function clear()
    {
        if ($this->dryRun) {
            return;
        }

        $database = $this->pdo->query('select database()')->fetchColumn();
        $quotedDatabase = $this->quoteIdentity($database);
        $this->pdo->exec("drop database if exists $quotedDatabase");
        $this->pdo->exec("create database $quotedDatabase");
        $this->pdo->exec("use $quotedDatabase");
    }
}
