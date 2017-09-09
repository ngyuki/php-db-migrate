<?php
namespace TestHelper;

use ngyuki\DbMigrate\Adapter\PdoMySqlAdapter;
use ngyuki\DbMigrate\Migrate\MigrateContext;
use PDO;
use ngyuki\DbMigrate\Console\ConfigLoader;
use ngyuki\DbMigrate\Migrate\Logger;
use Symfony\Component\Console\Output\NullOutput;

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

    public function pdo()
    {
        if (self::$pdo === null) {
            $host = getenv('MYSQL_HOST');
            $port = getenv('MYSQL_PORT');
            $name = getenv('MYSQL_DATABASE');
            $user = getenv('MYSQL_USER');
            $pass = getenv('MYSQL_PASSWORD');

            $dsn = sprintf("mysql:dbname=$name;host=$host;port=$port;charset=utf8");

            self::$pdo = new PDO($dsn, $user, $pass, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ));
        }

        return self::$pdo;
    }

    public function config()
    {
        $loader = new ConfigLoader();
        return $loader->load($this->configFile());
    }

    /**
     * @return Logger
     */
    public function logger()
    {
        return new Logger(new NullOutput());
    }

    /**
     * @return MigrateContext
     */
    public function context()
    {
        return new MigrateContext(
            $this->config(),
            $this->logger(),
            new PdoMySqlAdapter($this->pdo(), $this->logger())
        );
    }

    public function configFile()
    {
        return dirname(__DIR__) . '/_files/db-migrate.config.php';
    }

    public function files($name = null)
    {
        return dirname(__DIR__) . '/_files/' . $name;
    }

    public function read($name)
    {
        return file_get_contents($this->files($name));
    }
}
