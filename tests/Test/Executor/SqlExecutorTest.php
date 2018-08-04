<?php
namespace Test\Migrate;

use ngyuki\DbMigrate\Migrate\Logger;
use PDO;
use Symfony\Component\Console\Output\NullOutput;
use TestHelper\TestCase;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Executor\SqlExecutor;
use ngyuki\DbMigrate\Adapter\AdapterFactory;
use ngyuki\DbMigrate\Adapter\AdapterInterface;

class SqlExecutorTest extends TestCase
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    protected function setUp()
    {
        $this->pdo = (new TestEnv())->pdo();

        $this->pdo->query("drop table if exists tt");
        $this->pdo->query("create table tt (id int not null primary key)");

        $dryRun = false;
        $logger = new Logger(new NullOutput());

        $this->adapter = (new AdapterFactory())->create($this->pdo, $logger, $dryRun);
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
        $executor = new SqlExecutor($this->adapter);
        $executor->up(file_get_contents(__DIR__ . '/_files/exec.sql'));

        assertEquals(array("2000"), $this->fetch_list());
    }

    /**
     * @test
     */
    public function execute_down()
    {
        $this->pdo->query("insert into tt values ('2000')");

        $executor = new SqlExecutor($this->adapter);
        $executor->down(file_get_contents(__DIR__ . '/_files/exec.sql'));

        assertEmpty($this->fetch_list());
    }
}
