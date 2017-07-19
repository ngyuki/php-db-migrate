<?php
namespace Test\Migrate;

use ngyuki\DbMigrate\Adapter\Adapter;
use PDO;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Migrate\Config;
use ngyuki\DbMigrate\Executor\SqlExecutor;

class SqlExecutorTest extends \PHPUnit_Framework_TestCase
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
     * @var Config
     */
    private $config;

    protected function setUp()
    {
        $this->env = new TestEnv();
        $this->pdo = $this->env->pdo();
        $this->config = $this->env->config();

        $this->pdo->query("drop table if exists tt");
        $this->pdo->query("create table tt (id int not null primary key)");
    }

    private function fetch_list()
    {
        return $this->pdo->query("select id from tt order by id")->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @test
     */
    public function execute_up()
    {
        $executor = new SqlExecutor($this->env->logger(), new Adapter($this->env->pdo()), false);
        $executor->up($this->env->files('/ok/2000.sql'));

        assertEquals(array("2000"), $this->fetch_list());
    }

    /**
     * @test
     */
    public function execute_up_dryRun()
    {
        $executor = new SqlExecutor($this->env->logger(), new Adapter($this->env->pdo()), true);
        $executor->up($this->env->files('/ok/2000.sql'));

        assertEmpty($this->fetch_list());
    }

    /**
     * @test
     */
    public function execute_down()
    {
        $this->pdo->query("insert into tt values ('2000')");

        $executor = new SqlExecutor($this->env->logger(), new Adapter($this->env->pdo()), false);
        $executor->down($this->env->files('/ok/2000.sql'));

        assertEmpty($this->fetch_list());
    }

    /**
     * @test
     */
    public function execute_down_dryRun()
    {
        $this->pdo->query("insert into tt values ('2000')");

        $executor = new SqlExecutor($this->env->logger(), new Adapter($this->env->pdo()), true);
        $executor->down($this->env->files('/ok/2000.sql'));

        assertEquals(array('2000'), $this->fetch_list());
    }
}
