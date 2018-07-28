<?php
namespace Integration\MissingMigration;

use TestHelper\TestEnv;
use TestHelper\ApplicationTester;
use ngyuki\DbMigrate\Console\Application;

class MissingMigrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestEnv
     */
    private $env;

    /**
     * @var Application
     */
    private $app;

    /**
     * @var ApplicationTester
     */
    private $tester;

    public function setUp()
    {
        $this->env = new TestEnv();

        $this->env->clear();

        $this->app = new Application();
        $this->app->setCatchExceptions(false);
        $this->app->setAutoExit(false);

        $this->tester = new ApplicationTester($this->app);
    }

    /**
     * @test
     */
    public function test()
    {
        $this->tester->run('migrate', '--config', __DIR__ . '/config1.php');
        assertEquals(array('1000.sql', '2000.sql', '3000.sql'), $this->env->versions());


        $this->tester->run('migrate', '--config', __DIR__ . '/config2.php');
        assertEquals(array('1000.sql', '3000.sql', '4000.sql'), $this->env->versions());
    }
}
