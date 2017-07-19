<?php
namespace Test\Migrate;

use PDO;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Adapter\Adapter;

class AdapterTest extends \PHPUnit_Framework_TestCase
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

        $this->adapter = new Adapter($env->pdo());
        $this->pdo = $env->pdo();

        $this->adapter->dropTable();
    }

    /**
     * @test
     */
    public function create_and_drop()
    {
        // テーブルは無い
        assertThat($this->adapter->isExistTable(), isFalse());

        // テーブルが無くても失敗しない
        $this->adapter->dropTable();

        // テーブルを作成する
        $this->adapter->createTable();

        // テーブルが作成されている
        assertThat($this->adapter->isExistTable(), isTrue());

        // テーブルが作成されていても失敗しない
        $this->adapter->createTable();

        // テーブルを削除する
        $this->adapter->dropTable();

        // テーブルは無い
        assertThat($this->adapter->isExistTable(), isFalse());
    }

    /**
     * @test
     */
    public function save_fetch()
    {
        $this->adapter->save("987654");

        $rows = $this->pdo->query("select * from db_migrate order by version")->fetchAll(PDO::FETCH_COLUMN);

        assertEquals(array("987654"), $rows);

        $this->adapter->save("123456");

        $rows = $this->pdo->query("select * from db_migrate order by version")->fetchAll(PDO::FETCH_COLUMN);

        assertEquals(array("123456", "987654"), $rows);

        $rows = $this->adapter->fetchAll();

        assertEquals(array("123456", "987654"), array_keys($rows));
    }
}
