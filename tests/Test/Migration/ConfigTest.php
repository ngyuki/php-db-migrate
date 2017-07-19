<?php
namespace Test\Migrate;

use PDO;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Migrate\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
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
            'args' => $args,
        );

        $config = new Config($cfg, __FILE__);

        assertThat($config->pdo, isInstanceOf('PDO'));
        assertThat($config->args, identicalTo($args));
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

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Should be array is args.
     */
    public function validate_no_array_in_args()
    {
        $cfg = array(
            'pdo' => $this->pdo,
            'args' => new \stdClass(),
        );

        new Config($cfg, __FILE__);
    }
}
