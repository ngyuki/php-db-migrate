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

    /**
     * @var string
     */
    private $config;

    public function setUp()
    {
        $this->env = new TestEnv();
        $this->env->clear();

        $app = new Application();
        $app->setCatchExceptions(false);
        $app->setAutoExit(false);

        $this->tester = new ApplicationTester($app);

        $this->config = __DIR__ . '/_files';
    }

    /**
     * @test
     */
    public function status_()
    {
        $rc = $this->tester->run('status', '--config', $this->config);
        self::assertEquals(0, $rc);
    }

    /**
     * @test
     */
    public function migrate_()
    {
        $this->tester->run('migrate', '--config', $this->config);

        assertEquals(array("1000.sql", "3000.php", "9999.sql"), $this->env->versions());
    }

    /**
     * @test
     */
    public function up_down_()
    {
        $this->tester->run('up', '--config', $this->config);
        $this->tester->run('up', '--config', $this->config);
        assertEquals(["1000.sql", "3000.php"], $this->env->versions());

        $this->tester->run('down', '--config', $this->config);
        assertEquals(["1000.sql"], $this->env->versions());
    }

    /**
     * @test
     */
    public function up_down_all_()
    {
        $this->tester->run('up', '--config', $this->config, '--all');
        assertEquals(["1000.sql", "3000.php", "9999.sql"], $this->env->versions());

        $this->tester->run('down', '--config', $this->config, '--all');
        assertEquals([], $this->env->versions());
    }

    /**
     * @test
     */
    public function exec_()
    {
        $this->tester->run('mark', '--config', $this->config, '--all');
        $versions = $this->env->versions();

        $this->tester->run('exec', '--config', $this->config, __DIR__ . '/_files/migrations');

        $rows = $this->env->pdo()->query("select * from tt")->fetchAll(PDO::FETCH_COLUMN);
        assertEquals(['1000', '3000', '9999'], $rows);

        assertEquals($versions, $this->env->versions());
    }

    /**
     * @test
     */
    public function set_all()
    {
        $this->tester->run('set', '--all', '--config', $this->config);

        assertEquals(array("1000.sql", "3000.php", "9999.sql"), $this->env->versions());
    }

    /**
     * @test
     */
    public function set_one()
    {
        $this->tester->run('set', '3000.php', '--config', $this->config);

        assertEquals(array('3000.php'), $this->env->versions());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Please specify one of --all, version.
     */
    public function set_none()
    {
        $this->tester->run('set', '--config', $this->config);
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
        return array(
            array('set', '--config', $this->config, '--all', 'version'),
            array('set', '--config', $this->config, '--all', 'version'),
        );
    }

    /**
     * @test
     */
    public function unset_all()
    {
        $this->tester->run('set', '--config', $this->config, '--all');
        $this->tester->run('unset', '--config', $this->config, '--all');

        assertEmpty($this->env->versions());
    }

    /**
     * @test
     */
    public function unset_one()
    {
        $this->tester->run('set', '--config', $this->config, '--all');
        $this->tester->run('unset', '--config', $this->config, '3000.php');

        assertNotContains('3000.php', $this->env->versions());
    }

    /**
     * @test
     */
    public function clear_()
    {
        $rc = $this->tester->run('clear', '--config', $this->config);
        self::assertEquals(0, $rc);
    }
}
