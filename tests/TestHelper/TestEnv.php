<?php
namespace TestHelper;

use ngyuki\DbMigrate\Adapter\PdoMySqlAdapter;
use ngyuki\DbMigrate\Console\Configure;
use PDO;

class TestEnv
{
    private static $pdo;

    public $table;

    public static function create()
    {
        return new self;
    }

    public function __construct()
    {
        $this->table = PdoMySqlAdapter::TABLE_NAME;
    }

    public function clear()
    {
        $this->pdo()->query("drop table if exists tt");
        $this->pdo()->query("drop table if exists $this->table");
    }

    public function delete($versions)
    {
        if ($versions === null) {
            $this->pdo()->query("delete from $this->table");
        } else {
            $stmt = $this->pdo()->prepare("delete from $this->table where version = ?");
            foreach ($versions as $version) {
                $stmt->execute([$version]);
            }
        }
    }

    public function versions()
    {
        return $this->pdo()->query("select version from $this->table order by version")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return PDO
     */
    public function pdo()
    {
        if (self::$pdo === null) {
            $files = [
                    __DIR__ . '/../../sql/db-migrate.php',
                    __DIR__ . '/../../sql/db-migrate.php.dist',
            ];
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $config = require $file;
                    self::$pdo = $config['pdo'];
                    break;
                }
            }
        }

        return self::$pdo;
    }
}
