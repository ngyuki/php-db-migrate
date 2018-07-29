<?php
namespace Test\Migrate;

use ngyuki\DbMigrate\Console\ConfigLoader;
use ngyuki\DbMigrate\Migrate\ServiceLocator;
use ngyuki\DbMigrate\Migrate\Migration;
use PDO;
use PDOException;
use Symfony\Component\Console\Output\NullOutput;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Migrate\Config;

class MigratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestEnv
     */
    private $env;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ServiceLocator
     */
    private $locator;

    /**
     * @var PDO
     */
    private $pdo;

    protected function setUp()
    {
        $this->env = new TestEnv();
        $this->config =  (new ConfigLoader())->load(__DIR__ . '/_files');
        $this->pdo = $this->env->pdo();

        $this->initServiceLocator();

        $this->env->clear();
    }

    public function initServiceLocator($dryRun = false)
    {
        $this->locator = new ServiceLocator(new NullOutput(), $this->config, $dryRun);
    }

    /**
     * @test
     */
    public function migrate_error()
    {
        $migrations = [
            '0000.sql' => (new Migration())->setScript(__DIR__ . '/_files/err/0000.sql'),
            '1000.sql' => (new Migration())->setScript(__DIR__ . '/_files/err/1000.sql'),
            '2000.sql' => (new Migration())->setScript(__DIR__ . '/_files/err/2000.sql'),
            '3000.sql' => (new Migration())->setScript(__DIR__ . '/_files/err/3000.sql'),
        ];

        try {
            $this->locator->migrator->doMigrate($migrations, $migrations, []);
            $this->fail();
        } catch (PDOException $ex) {
            assertContains('Duplicate entry', $ex->getMessage());
        }

        $rows = $this->pdo->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(array(1000), $rows);

        // エラーの直前までのバージョンが適用済
        $list = $this->env->versions();
        assertEquals(array("0000.sql", "1000.sql"), $list);
    }
}
