<?php
namespace TestHelper;

use PDO;
use ngyuki\DbMigrate\Migrate\ConfigLoader;
use ngyuki\DbMigrate\Migrate\Logger;
use Symfony\Component\Console\Output\NullOutput;

class TestEnv
{
    private static $pdo;

    public static function create()
    {
        return new self;
    }

    public function pdo()
    {
        if (self::$pdo === null) {
            $host = getenv('DB_HOST');
            $port = getenv('DB_PORT');
            $name = getenv('DB_NAME');
            $user = getenv('DB_USER');
            $pass = getenv('DB_PASS');

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

    public function configFile()
    {
        return dirname(__DIR__) . '/_files/db-migrate.config.php';
    }

    public function files($name = null)
    {
        return dirname(__DIR__) . '/_files/' . $name;
    }
}
