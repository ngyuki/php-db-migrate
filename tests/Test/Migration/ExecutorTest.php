<?php
namespace Test\Migrate;

use PDO;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Migrate\Executor;
use ngyuki\DbMigrate\Migrate\Config;

class ExecutorTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @var Executor
     */
    private $executor;

    protected function setUp()
    {
        $this->env = new TestEnv();
        $this->pdo = $this->env->pdo();
        $this->config = $this->env->config();

        $this->executor = new Executor($this->config, $this->env->logger());

        $this->pdo->query("drop table if exists tt");
        $this->pdo->query("create table tt (id int not null primary key)");
    }

    private function fetch_list()
    {
        return $this->pdo->query("select id from tt order by id")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @test
     */
    function execute_sql()
    {
        $fn = $this->env->files('/ok/2000.sql');
        $this->executor->execute($fn);

        assertEquals(array("2000"), $this->fetch_list());
    }

    /**
     * @test
     */
    function execute_sql_dryrun()
    {
        $this->config->dryRun = true;
        $this->executor = new Executor($this->config, $this->env->logger());

        $fn = $this->env->files('/ok/2000.sql');
        $this->executor->execute($fn);

        assertEmpty($this->fetch_list());
    }

    /**
     * @test
     */
    function execute_php()
    {
        $fn = $this->env->files('/ok/3000.php');
        $this->executor->execute($fn);

        assertEquals(array("3000"), $this->fetch_list());
    }

    /**
     * @test
     */
    function execute_php_dryrun()
    {
        $this->config->dryRun = true;
        $this->executor = new Executor($this->config, $this->env->logger());

        $fn = $this->env->files('/ok/3000.php');
        $this->executor->execute($fn);

        assertEmpty($this->fetch_list());
    }

    /**
     * @test
     */
    function execute_sql_php()
    {
        $this->pdo->exec("DROP PROCEDURE IF EXISTS sp_sample");

        $fn = $this->env->files('/sp/0000.php.sql');
        $this->executor->execute($fn);

        $row = $this->pdo->query("call sp_sample()")->fetch();
        assertEquals(array('one' => 1), $row);
    }
}
