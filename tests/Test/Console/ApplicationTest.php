<?php
namespace Test\Console;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
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

    public function setUp()
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
    public function status_()
    {
        $fn = $this->env->files();
        $this->tester->run('status', '--config', $fn);
    }

    /**
     * @test
     */
    public function migrate_()
    {
        $fn = $this->env->files();
        $this->tester->run('migrate', '--config', $fn);
    }

    /**
     * @test
     */
    public function exec_()
    {
        $fn = $this->env->files();
        $dir = $this->env->files('ok');
        $this->tester->run('exec', '--config', $fn, $dir);
    }

    /**
     * @test
     */
    public function set_all()
    {
        $fn = $this->env->files();

        $this->tester->run('set', '--all', '--config', $fn);

        $sql = "select version from db_migrate order by version";
        $list = $this->pdo->query($sql)->fetchAll(\PDO::FETCH_COLUMN);
        assertEquals(array("1000.sql", "2000.sql", "3000.php", "9999.sql"), $list);
    }

    /**
     * @test
     */
    public function set_one()
    {
        $fn = $this->env->files();

        $this->tester->run('set', '2000.sql', '--config', $fn);

        $version = $this->pdo->query("select version from db_migrate")->fetchColumn();
        assertEquals('2000.sql', $version);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Please specify one of --all, version.
     */
    public function set_none()
    {
        $fn = $this->env->files();
        $this->tester->run('set', '--config', $fn);
    }

    /**
     * @test
     * @dataProvider set_too_many_args_data
     * @expectedException \RuntimeException
     * @expectedExceptionMessage You can specify that only one --all, version.
     */
    public function set_too_many_args()
    {
        $this->tester->runArgs(func_get_args());
    }

    public function set_too_many_args_data()
    {
        $env = new TestEnv();
        $fn = $env->files();

        return array(
            array('set', '--config', $fn, '--all', 'version'),
            array('set', '--config', $fn, '--all', 'version'),
        );
    }

    /**
     * @test
     */
    public function unset_all()
    {
        $fn = $this->env->files();

        $this->tester->run('set', '--config', $fn, '--all');
        $this->tester->run('unset', '--config', $fn, '--all');

        $rows = $this->pdo->query("show tables like 'migrate'")->fetchAll();
        assertEmpty($rows);
    }

    /**
     * @test
     */
    public function unset_one()
    {
        $fn = $this->env->files();

        $this->tester->run('set', '--config', $fn, '--all');
        $this->tester->run('unset', '--config', $fn, '2000.sql');
    }
}
