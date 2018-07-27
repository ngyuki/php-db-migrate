<?php
namespace Test\Migrate;

use ngyuki\DbMigrate\Console\ConfigLoader;
use ngyuki\DbMigrate\Migrate\Logger;
use ngyuki\DbMigrate\Migrate\ServiceLocator;
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
        $this->locator = new ServiceLocator(new Logger(new NullOutput()), $this->config, $dryRun);
    }

    /**
     * @test
     */
    public function migrate_nothing()
    {
        // すべてのバージョンが適用済とする
        $this->locator->migrator->markAllVersions();

        // すべてのバージョンがテーブルに記録されている
        $list = $this->env->versions();
        assertEquals(array("1000.sql", "2000.sql", "3000.php", "9999.sql"), $list);

        // マイグレーションを実行
        $this->locator->migrator->migrate();

        // なにもおこらない
        $rows = $this->pdo->query("show tables like 'tt'")->fetchAll();
        assertEmpty($rows);
    }

    /**
     * @test
     */
    public function migrate_()
    {
        $this->locator->migrator->migrate();

        $rows = $this->pdo->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(array(1000, 1001, 2000, 3000, 9999), $rows);

        $list = $this->env->versions();
        assertEquals(array("1000.sql", "2000.sql", "3000.php", "9999.sql"), $list);
    }

    /**
     * @test
     */
    public function migrate_target()
    {
        $this->locator->migrator->migrate('3000.php');

        $rows = $this->pdo->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(array(1000, 1001, 2000, 3000), $rows);

        $list = $this->env->versions();
        assertEquals(array('1000.sql', '2000.sql', '3000.php'), $list);

        $this->locator->migrator->migrate('1000.sql');

        $rows = $this->pdo->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(array(1000, 1001), $rows);

        $list = $this->env->versions();
        assertEquals(array('1000.sql'), $list);
    }

    /**
     * @test
     */
    public function migrate_dryRun()
    {
        $this->initServiceLocator(true);

        $this->locator->migrator->migrate();

        $rows = $this->pdo->query("show tables like 'tt'")->fetchAll();
        assertEmpty($rows);

        $rows = $this->pdo->query("show tables like 'migrate'")->fetchAll();
        assertEmpty($rows);
    }

    /**
     * @test
     */
    public function exec_()
    {
        $this->locator->migrator->markAllVersions();
        $prev = $this->env->versions();

        $this->locator->migrator->exec(__DIR__ . '/_files/ok2');

        $rows = $this->pdo->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(array(1, 2), $rows);

        $next = $this->env->versions();
        assertEquals($prev, $next);
    }

    /**
     * @test
     */
    public function migrate_delete_record()
    {
        $this->locator->migrator->markAllVersions();

        // マイグレーションのレコードを削除する
        $this->env->delete(null);

        $this->locator->migrator->migrate();

        // すべてのバージョンが適用される
        $rows = $this->pdo->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(array(1000, 1001, 2000, 3000, 9999), $rows);

        // すべてのバージョンが適用済
        $list = $this->env->versions();
        assertEquals(array("1000.sql", "2000.sql", "3000.php", "9999.sql"), $list);
    }

    /**
     * @test
     */
    public function migrate_part()
    {
        $this->locator->migrator->markAllVersions();

        // 2000.sql と 3000.php をマイグレーションテーブルから削除する
        $this->env->delete(array('2000.sql', '3000.php'));

        $this->pdo->query("create table tt ( id int not null primary key )");

        $this->locator->migrator->migrate();

        $rows = $this->pdo->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(array(2000, 3000), $rows);

        // すべてのバージョンが適用済
        $list = $this->env->versions();
        assertEquals(array("1000.sql", "2000.sql", "3000.php", "9999.sql"), $list);
    }

    /**
     * @test
     */
    public function migrate_error()
    {
        $this->config->scriptDirectory = __DIR__ . '/_files/err';
        $this->initServiceLocator();

        try {
            $this->locator->migrator->migrate();
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

    /**
     * @test
     */
    public function mark_all_versions()
    {
        $this->locator->migrator->markAllVersions();

        $list = $this->env->versions();
        assertEquals(array("1000.sql", "2000.sql", "3000.php", "9999.sql"), $list);
    }

    /**
     * @test
     */
    public function unmark_all_versions()
    {
        $this->locator->migrator->markAllVersions();
        $this->locator->migrator->unmarkAllVersions();

        $list = $this->env->versions();
        assertEquals(array(), $list);
    }

    /**
     * @test
     */
    public function up_down_()
    {
        $this->locator->migrator->up();

        assertEquals(array('1000.sql'), $this->env->versions());

        $this->locator->migrator->markVersion('3000.php');
        $this->locator->migrator->up();

        assertEquals(array('1000.sql', '2000.sql', '3000.php'), $this->env->versions());

        $this->locator->migrator->unmarkVersion('2000.sql');
        $this->locator->migrator->down();

        assertEquals(array('1000.sql'), $this->env->versions());

        $this->locator->migrator->down();

        assertEquals(array(), $this->env->versions());
    }

    /**
     * @test
     */
    public function clear_()
    {
        $this->locator->migrator->up();
        $this->locator->migrator->clear();
        $this->locator->migrator->up();

        $roes = $this->env->versions();
        assertCount(1, $roes);
    }

    /**
     * @test
     */
    public function clear_dryRun()
    {
        $this->locator->migrator->up();

        $this->initServiceLocator();

        $roes = $this->env->versions();
        assertCount(1, $roes);
    }
}
