<?php
namespace TestHelper;

use PDO;

class TestEnv
{
    private static $pdo;

    public static function create()
    {
        return new self;
    }

    public function clear()
    {
        $this->pdo()->query("drop table if exists tt");
        $this->pdo()->query("drop table if exists migration");
    }

    public function delete($versions)
    {
        if ($versions === null) {
            $this->pdo()->query("delete from migration");
        } else {
            $stmt = $this->pdo()->prepare("delete from migration where version = ?");
            foreach ($versions as $version) {
                $stmt->execute([$version]);
            }
        }
    }

    public function versions()
    {
        return $this->pdo()->query("select version from migration order by version")
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
