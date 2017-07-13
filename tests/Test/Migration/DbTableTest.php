<?php
namespace Test\Migrate;

use PDO;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Migrate\DbTable;

class DbTableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DbTable
     */
    private $table;

    /**
     * @var PDO
     */
    private $pdo;

    protected function setUp()
    {
        $env = TestEnv::create();

        $this->table = new DbTable($env->config());
        $this->pdo = $env->pdo();

        $this->pdo->query("drop table if exists db_migrate");
    }

    /**
     * @test
     */
    public function create_and_drop()
    {
        // テーブルは無い
        assertThat($this->table->isExistTable(), isFalse());

        // テーブルが無くても失敗しない
        $this->table->dropTable();

        // テーブルを作成する
        $this->table->createTable();

        // テーブルが作成されている
        assertThat($this->table->isExistTable(), isTrue());

        // テーブルが作成されていても失敗しない
        $this->table->createTable();

        // テーブルを削除する
        $this->table->dropTable();

        // テーブルは無い
        assertThat($this->table->isExistTable(), isFalse());
    }

    /**
     * @test
     */
    public function fixVersion_getAppliedList()
    {
        $this->table->fixVersion("987654");

        $rows = $this->pdo->query("select * from db_migrate")->fetchAll();

        assertCount(1, $rows);
        assertEquals("987654", $rows[0]['version']);

        $this->table->fixVersion("123456");

        $rows = $this->pdo->query("select * from db_migrate order by version")->fetchAll();

        assertCount(2, $rows);
        assertEquals("123456", $rows[0]['version']);
        assertEquals("987654", $rows[1]['version']);

        $rows = $this->table->getAppliedList();

        assertCount(2, $rows);
        assertEquals("123456", $rows[0]['version']);
        assertEquals("987654", $rows[1]['version']);
    }

    /**
     * @test
     */
    public function isApplied_()
    {
        assertThat($this->table->isApplied("987654"), isFalse());

        $this->table->fixVersion("987654");

        assertThat($this->table->isApplied("987654"), isTrue());
        assertThat($this->table->isApplied("123456"), isFalse());
    }
}
