<?php
namespace Test\Migrate;

use PDO;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Migrate\Config;
use ngyuki\DbMigrate\Migrate\Manager;

class ManagerTest extends \PHPUnit_Framework_TestCase
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
     * @var Manager
     */
    private $manager;

    /**
     * @var PDO
     */
    private $pdo;

    protected function setUp()
    {
        $this->env = new TestEnv();

        $this->config = $this->env->config();

        $this->pdo = $this->env->pdo();

        $this->manager = new Manager($this->config, $this->env->logger());

        $this->pdo->query("drop table if exists tt");
        $this->pdo->query("drop table if exists db_migrate");
    }

    private function fetch_migrate_versions()
    {
        return $this->pdo->query("select version from db_migrate order by version")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @test
     */
    function migrate_nothing()
    {
        // すべてのバージョンが適用済とする
        $this->manager->fixAllVersions();

        // すべてのバージョンがテーブルに記録されている
        $list = $this->fetch_migrate_versions();
        assertEquals(array("1000.sql", "2000.sql", "3000.php", "9999.sql"), $list);

        // マイグレーションを実行
        $this->manager->migrate();

        // なにもおこらない
        $rows = $this->pdo->query("show tables like 'tt'")->fetchAll();
        assertEmpty($rows);
    }

    /**
     * @test
     */
    function migrate_()
    {
        $this->manager->migrate();

        $rows = $this->pdo->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(array(1000, 1001, 2000, 3000), $rows);

        $list = $this->fetch_migrate_versions();
        assertEquals(array("1000.sql", "2000.sql", "3000.php", "9999.sql"), $list);
    }

    /**
     * @test
     */
    function migrate_dryRun()
    {
        $this->config->dryRun = true;
        $this->manager = new Manager($this->config, $this->env->logger());

        $this->manager->migrate();

        $rows = $this->pdo->query("show tables like 'tt'")->fetchAll();
        assertEmpty($rows);

        $rows = $this->pdo->query("show tables like 'migrate'")->fetchAll();
        assertEmpty($rows);
    }

    /**
     * @test
     */
    function migrate_noFix()
    {
        $this->manager->fixAllVersions();
        $prev = $this->fetch_migrate_versions();

        $this->manager->setScriptDirectory($this->env->files('ok2'));
        $this->manager->migrate(true);

        $rows = $this->pdo->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(array(1, 2), $rows);

        $next = $this->fetch_migrate_versions();
        assertEquals($prev, $next);
    }

    /**
     * @test
     */
    function migrate_delete_record()
    {
        $this->manager->fixAllVersions();

        // マイグレーションのレコードを削除する
        $this->pdo->query("delete from db_migrate");

        $this->manager->migrate();

        // すべてのバージョンが適用される
        $rows = $this->pdo->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(array(1000, 1001, 2000, 3000), $rows);

        // すべてのバージョンが適用済
        $list = $this->fetch_migrate_versions();
        assertEquals(array("1000.sql", "2000.sql", "3000.php", "9999.sql"), $list);
    }

    /**
     * @test
     */
    function migrate_part()
    {
        $this->manager->fixAllVersions();

        // 2000.sql と 3000.php をマイグレーションテーブルから削除する
        $this->pdo->query("delete from db_migrate where version in ('2000.sql', '3000.php')");

        $this->pdo->query("create table tt ( id int not null primary key )");

        $this->manager->migrate();

        $rows = $this->pdo->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(array(2000, 3000), $rows);

        // すべてのバージョンが適用済
        $list = $this->fetch_migrate_versions();
        assertEquals(array("1000.sql", "2000.sql", "3000.php", "9999.sql"), $list);
    }

    /**
     * @test
     */
    function migrate_error()
    {
        $this->manager->setScriptDirectory($this->env->files('err'));

        try
        {
            $this->manager->migrate();
            $this->fail();
        }
        catch (\Doctrine\DBAL\DBALException $ex)
        {
            assertContains('Duplicate entry', $ex->getMessage());
        }

        $rows = $this->pdo->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(array(1000), $rows);

        // エラーの直前までのバージョンが適用済
        $list = $this->fetch_migrate_versions();
        assertEquals(array("0000.sql", "1000.sql"), $list);
    }

    /**
     * @test
     */
    function fix_all_versions()
    {
        $this->manager->fixAllVersions();

        $list = $this->fetch_migrate_versions();
        assertEquals(array("1000.sql", "2000.sql", "3000.php", "9999.sql"), $list);
    }

    /**
     * @test
     */
    function status_()
    {
        $re = $this->manager->showStatus();
        assertEquals(1, $re);

        $this->manager->migrate();

        $re = $this->manager->showStatus();
        assertEquals(0, $re);
    }
}
