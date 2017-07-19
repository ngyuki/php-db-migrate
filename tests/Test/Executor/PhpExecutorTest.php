<?php
namespace Test\Migrate;

use PDO;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Executor\PhpExecutor;

class PhpExecutorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestEnv
     */
    private $env;

    /**
     * @var PDO
     */
    private $pdo;

    protected function setUp()
    {
        $this->env = new TestEnv();
        $this->pdo = $this->env->pdo();

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
        $executor = new PhpExecutor($this->env->logger(), array($this->pdo), false);
        $executor->up($this->env->files('/ok/3000.php'));

        assertEquals(array('3000'), $this->fetch_list());
    }

    /**
     * @test
     */
    public function execute_up_dryRun()
    {
        $executor = new PhpExecutor($this->env->logger(), array($this->pdo), true);
        $executor->up($this->env->files('/ok/3000.php'));

        assertEmpty($this->fetch_list());
    }

    /**
     * @test
     */
    public function execute_down()
    {
        $this->pdo->query("insert into tt values ('3000')");

        $executor = new PhpExecutor($this->env->logger(), array($this->pdo), false);
        $executor->down($this->env->files('/ok/3000.php'));

        assertEmpty($this->fetch_list());
    }

    /**
     * @test
     */
    public function execute_down_dryRun()
    {
        $this->pdo->query("insert into tt values ('3000')");

        $executor = new PhpExecutor($this->env->logger(), array($this->pdo), true);
        $executor->down($this->env->files('/ok/3000.php'));

        assertEquals(array('3000'), $this->fetch_list());
    }
}
