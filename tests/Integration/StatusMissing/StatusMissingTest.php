<?php
namespace Integration\StatusMissing;

use TestHelper\TestCase;
use TestHelper\TestEnv;
use TestHelper\ApplicationTester;
use ngyuki\DbMigrate\Console\Application;

class StatusMissingTest extends TestCase
{
    /**
     * @var TestEnv
     */
    private static $env;

    /**
     * @var Application
     */
    private $app;

    /**
     * @var ApplicationTester
     */
    private $tester;

    public static function setUpBeforeClass()
    {
        self::$env = new TestEnv();
        self::$env->clear();
    }

    public function setUp()
    {
        $this->app = new Application();
        $this->app->setCatchExceptions(false);
        $this->app->setAutoExit(false);

        $this->tester = new ApplicationTester($this->app);
    }

    /**
     * @test
     */
    public function init()
    {
        $ret = $this->tester->run('migrate', '--config', __DIR__ . '/config1.php');
        assertThat(explode("\n", trim($this->tester->getOutput())), equalTo([
            'up: 1000.sql',
            'up: 2000.sql',
            'up: 3000.sql',
        ]));
        assertSame($ret, 0);

        $ret = $this->tester->run('status', '--config', __DIR__ . '/config1.php');
        assertThat(explode("\n", trim($this->tester->getOutput())), equalTo([
            '[*] 1000.sql',
            '[*] 2000.sql',
            '[*] 3000.sql',
        ]));
        assertSame($ret, 0);

        $ret = $this->tester->run('status', '--missing', '--config', __DIR__ . '/config1.php');
        assertThat(explode("\n", trim($this->tester->getOutput())), equalTo([
            '[*] 1000.sql',
            '[*] 2000.sql',
            '[*] 3000.sql',
        ]));
        assertSame($ret, 0);
    }

    /**
     * @test
     */
    public function has_no_applied()
    {
        $ret = $this->tester->run('status', '--config', __DIR__ . '/config2.php');
        assertThat(explode("\n", trim($this->tester->getOutput())), equalTo([
            '[*] 1000.sql',
            '[*] 2000.sql',
            '[*] 3000.sql',
            '[ ] 4000.sql',
        ]));
        assertSame($ret, 1);

        $ret = $this->tester->run('status', '--missing', '--config', __DIR__ . '/config2.php');
        assertThat(explode("\n", trim($this->tester->getOutput())), equalTo([
            '[*] 1000.sql',
            '[*] 2000.sql',
            '[*] 3000.sql',
            '[ ] 4000.sql',
        ]));
        assertSame($ret, 1);
    }

    /**
     * @test
     */
    public function has_missing()
    {
        $ret = $this->tester->run('status', '--config', __DIR__ . '/config3.php');
        assertThat(explode("\n", trim($this->tester->getOutput())), equalTo([
            '[*] 1000.sql',
            '[*] 2000.sql (missing)',
            '[*] 3000.sql',
        ]));
        assertSame($ret, 0);

        $ret = $this->tester->run('status', '--missing', '--config', __DIR__ . '/config3.php');
        assertThat(explode("\n", trim($this->tester->getOutput())), equalTo([
            '[*] 1000.sql',
            '[*] 2000.sql (missing)',
            '[*] 3000.sql',
        ]));
        assertSame($ret, 1);
    }
}
