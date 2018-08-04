<?php
namespace Test\Migrate;

use PDO;
use TestHelper\TestCase;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Migrate\Config;

class ConfigTest extends TestCase
{
    /**
     * @var TestEnv
     */
    private $env;

    /**
     * @var PDO
     */
    private $pdo;

    protected function setUp()
    {
        $this->env = new TestEnv();
        $this->pdo = $this->env->pdo();
    }

    /**
     * @test
     */
    public function construct_()
    {
        $args = array(
            'obj' => new \stdClass(),
        );

        $cfg = array(
            'pdo' => $this->pdo,
        );

        $config = new Config($cfg, __FILE__);

        assertThat($config->pdo, isInstanceOf('PDO'));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Should be PDO is pdo.
     */
    public function validate_no_Adapter()
    {
        $cfg = array();

        new Config($cfg, __FILE__);
    }
}
