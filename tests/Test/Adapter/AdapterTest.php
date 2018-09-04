<?php
namespace Test\Migrate;

use ngyuki\DbMigrate\Driver\PdoMySqlDriver;
use ngyuki\DbMigrate\Migrate\Logger;
use PDO;
use Symfony\Component\Console\Output\NullOutput;
use TestHelper\TestCase;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Adapter\Adapter;

class AdapterTest extends TestCase
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var PDO
     */
    private $pdo;

    protected function setUp()
    {
        $env = TestEnv::create();
        $env->clear();

        $dryRun = false;
        $this->adapter = new Adapter(new PdoMySqlDriver($env->pdo()), new Logger(new NullOutput()), $dryRun, 'migration');
        $this->pdo = $env->pdo();
    }

    /**
     * @test
     */
    public function save_fetch()
    {
        $this->adapter->save("987654", "aaa");

        $rows = array_keys($this->adapter->fetchAll());

        assertEquals(array("987654"), $rows);

        $this->adapter->save("123456", "xxx");

        $rows = array_keys($this->adapter->fetchAll());

        assertEquals(array("123456", "987654"), $rows);

        $rows = $this->adapter->fetchAll();

        assertEquals(array("123456", "987654"), array_keys($rows));
    }

    /**
     * @test
     */
    public function clear_()
    {
        $this->adapter->clear();

        $rows = $this->pdo->query('show tables')->fetchAll();
        assertEmpty($rows);
    }
}
