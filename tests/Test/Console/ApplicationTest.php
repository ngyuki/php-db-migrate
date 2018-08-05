<?php
namespace Test\Console;

use PDO;
use TestHelper\TestCase;
use TestHelper\TestEnv;
use TestHelper\ApplicationTester;
use ngyuki\DbMigrate\Console\Application;

class ApplicationTest extends TestCase
{
    /**
     * @var TestEnv
     */
    private $env;

    /**
     * @var ApplicationTester
     */
    private $tester;

    public function setUp()
    {
        $this->env = new TestEnv();
        $this->env->clear();

        $app = new Application();
        $app->setCatchExceptions(false);
        $app->setAutoExit(false);

        $this->tester = new ApplicationTester($app);

        chdir(__DIR__ . '/_files/');
    }

    /**
     * @test
     */
    public function status_()
    {
        $rc = $this->tester->run('status');
        self::assertEquals(0, $rc);
    }

    /**
     * @test
     */
    public function migrate_()
    {
        $this->tester->run('migrate');

        assertEquals(array("1000.sql", "3000.php", "9999.sql"), $this->env->versions());
    }

    /**
     * @test
     */
    public function up_down_()
    {
        $this->tester->run('up');
        $this->tester->run('up');
        assertEquals(["1000.sql", "3000.php"], $this->env->versions());

        $this->tester->run('down');
        assertEquals(["1000.sql"], $this->env->versions());
    }

    /**
     * @test
     */
    public function up_down_all_()
    {
        $this->tester->run('up', '--all');
        assertEquals(["1000.sql", "3000.php", "9999.sql"], $this->env->versions());

        $this->tester->run('down', '--all');
        assertEquals([], $this->env->versions());
    }

    /**
     * @test
     */
    public function exec_()
    {
        $this->tester->run('mark', '--all');
        $versions = $this->env->versions();

        $this->tester->run('exec', __DIR__ . '/_files/migrations');

        $rows = $this->env->pdo()->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(['1000', '3000', '9999'], $rows);

        assertEquals($versions, $this->env->versions());
    }

    /**
     * @test
     */
    public function mark_all()
    {
        $this->tester->run('mark', '--all');

        assertEquals(array("1000.sql", "3000.php", "9999.sql"), $this->env->versions());
    }

    /**
     * @test
     */
    public function mark_one()
    {
        $this->tester->run('mark', '3000.php');

        assertEquals(array('3000.php'), $this->env->versions());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Please specify one of --all, version.
     */
    public function mark_none()
    {
        $this->tester->run('mark');
    }

    /**
     * @test
     * @dataProvider mark_too_many_args_data
     * @expectedException \RuntimeException
     * @expectedExceptionMessage You can specify that only one --all, version.
     */
    public function mark_too_many_args()
    {
        $args = func_get_args();
        $this->tester->runArgs($args);
    }

    public function mark_too_many_args_data()
    {
        return array(
            array('mark', '--all', 'version'),
            array('mark', '--all', 'version'),
        );
    }

    /**
     * @test
     */
    public function unmark_all()
    {
        $this->tester->run('mark', '--all');
        $this->tester->run('unmark', '--all');

        assertEmpty($this->env->versions());
    }

    /**
     * @test
     */
    public function unmark_one()
    {
        $this->tester->run('mark', '--all');
        $this->tester->run('unmark', '3000.php');

        assertNotContains('3000.php', $this->env->versions());
    }

    /**
     * @test
     */
    public function clear_()
    {
        $rc = $this->tester->run('clear');
        self::assertEquals(0, $rc);
    }
}
