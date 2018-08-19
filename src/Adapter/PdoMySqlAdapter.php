<?php
namespace ngyuki\DbMigrate\Adapter;

use ngyuki\DbMigrate\Migrate\Logger;
use PDO;

class PdoMySqlAdapter implements AdapterInterface
{
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

    /**
     * @var string
     */
    private $table;

    public function __construct(\PDO $pdo, Logger $logger, $dryRun, $table)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
        $this->dryRun = $dryRun;
        $this->table = $table;
    }

    private function quoteIdentity($name)
    {
        return '`' . strtr($name, '`', '``') . '`';
    }

    private function quotedTable()
    {
        return $this->table;
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
                $this->pdo->exec($sql);
            }
        } else {
            $log = $sql . ' ' . json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $this->logger->verbose($log);
            if (!$this->dryRun) {
                $this->pdo->prepare($sql)->execute($params);
            }
        }
    }

    public function createTable()
    {
        $stmt = $this->pdo->query('show tables');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (in_array($this->table, $tables)) {
            return;
        }

        $sql = "
            create table {$this->quotedTable()} (
                version varchar (255) not null,
                apply_at datetime not null,
                content LONGBLOB,
                primary key (version) 
            )
        ";

        $this->exec($sql);
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
        $database = $this->pdo->query('select database()')->fetchColumn();
        $quotedDatabase = $this->quoteIdentity($database);

        $this->exec("drop database if exists $quotedDatabase");
        $this->exec("create database $quotedDatabase");
        $this->exec("use $quotedDatabase");
    }
}
