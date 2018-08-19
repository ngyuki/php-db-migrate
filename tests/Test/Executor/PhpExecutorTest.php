<?php
namespace Test\Migrate;

use ngyuki\DbMigrate\Adapter\PdoMySqlAdapter;
use ngyuki\DbMigrate\Migrate\Logger;
use ngyuki\DbMigrate\Migrate\MigrationContext;
use ngyuki\DbMigrate\Migrate\MigrationContextImpl;
use PDO;
use Symfony\Component\Console\Output\NullOutput;
use TestHelper\TestCase;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Executor\PhpExecutor;

class PhpExecutorTest extends TestCase
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var MigrationContext
     */
    private $context;

    protected function setUp()
    {
        $this->pdo = (new TestEnv())->pdo();

        $this->pdo->query("drop table if exists tt");
        $this->pdo->query("create table tt (id int not null primary key)");

        $this->context = $this->createContext();
    }

    private function createContext($dryRun = false)
    {
        $logger = new Logger(new NullOutput());
        $adapter = new PdoMySqlAdapter($this->pdo, $logger, $dryRun, 'migration');

        $config = [
            'ore' => 111,
            \PDO::class => $this->pdo,
        ];

        return new MigrationContextImpl($config, $dryRun, $logger, $adapter);
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
        $executor = new PhpExecutor($this->context);
        $executor->up(file_get_contents(__DIR__ . '/_files/exec.php'));

        assertEquals(array('111', '666', '777', '888', '999'), $this->fetch_list());
    }

    /**
     * @test
     */
    public function execute_dryRun()
    {
        $this->context = $this->createContext(true);

        $executor = new PhpExecutor($this->context);
        $executor->up(file_get_contents(__DIR__ . '/_files/exec.php'));

        assertEmpty($this->fetch_list());
    }

    /**
     * @test
     */
    public function execute_down()
    {
        $this->pdo->query("insert into tt values ('111')");
        $this->pdo->query("insert into tt values ('999')");

        $executor = new PhpExecutor($this->context);
        $executor->down(file_get_contents(__DIR__ . '/_files/exec.php'));

        assertEmpty($this->fetch_list());
    }
}
