<?php
namespace Test\Console;

use PDO;
use TestHelper\TestEnv;
use TestHelper\ApplicationTester;
use ngyuki\DbMigrate\Console\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestEnv
     */
    private $env;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var Application
     */
    private $app;

    /**
     * @var ApplicationTester
     */
    private $tester;

    function setUp()
    {
        $this->env = new TestEnv();

        $this->pdo = $this->env->pdo();
        $this->pdo->query("drop table if exists tt");
        $this->pdo->query("drop table if exists db_migrate");

        $this->app = new Application();
        $this->app->setCatchExceptions(false);
        $this->app->setAutoExit(false);

        $this->tester = new ApplicationTester($this->app);
    }

    /**
     * @test
     */
    function status_()
    {
        $fn = $this->env->files();
        $this->tester->run('status', '--config', $fn);
    }

    /**
     * @test
     */
    function migrate_()
    {
        $fn = $this->env->files();
        $this->tester->run('migrate', '--config', $fn);
    }

    /**
     * @test
     */
    function exec_()
    {
        $fn = $this->env->files();
        $dir = $this->env->files('ok');
        $this->tester->run('exec', '--config', $fn, $dir);
    }

    /**
     * @test
     */
    function fix_()
    {
        $fn = $this->env->files();

        ///

        $this->tester->run('fix', '--all', '--config', $fn);

        $sql = "select version from db_migrate order by version";
        $list = $this->pdo->query($sql)->fetchAll(\PDO::FETCH_COLUMN);
        assertEquals(array("1000.sql", "2000.sql", "3000.php", "9999.sql"), $list);

        ///

        $this->tester->run('fix', '--clear', '--config', $fn);

        $rows = $this->pdo->query("show tables like 'migrate'")->fetchAll();
        assertEmpty($rows);

        ///

        $this->tester->run('fix', '2000.sql', '--config', $fn);

        $version = $this->pdo->query("select * from db_migrate")->fetchColumn();
        assertEquals('2000.sql', $version);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Please specify one of --all, --clear, version.
     */
    function fix_none()
    {
        $fn = $this->env->files();
        $this->tester->run('fix', '--config', $fn);
    }

    /**
     * @test
     * @dataProvider fix_too_dataProvider
     * @expectedException \RuntimeException
     * @expectedExceptionMessage You can specify that only one --all, --clear, version.
     */
    function fix_too()
    {
        $this->tester->runArgs(func_get_args());
    }

    function fix_too_dataProvider()
    {
        $env = new TestEnv();
        $fn = $env->files();

        return array(
            array('fix', '--config', $fn, '--all', '--clear', 'version'),
            array('fix', '--config', $fn, '--all', '--clear'),
            array('fix', '--config', $fn, '--all', 'version'),
            array('fix', '--config', $fn, '--clear', 'version'),
        );
    }
}
