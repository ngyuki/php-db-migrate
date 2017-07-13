<?php
namespace Test\Migrate;

use PDO;
use TestHelper\TestEnv;
use ngyuki\DbMigrate\Migrate\Config;
use ngyuki\DbMigrate\Adapter\PdoAdapter;

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
        $extract = array(
            'obj' => new \stdClass(),
        );

        $cfg = array(
            'pdo' => $this->pdo,
            'extract' => $extract,
        );

        $config = new Config($cfg, __FILE__);

        $adapter = new PdoAdapter($this->pdo);

        assertThat($config->adapter, isInstanceOf(get_class($adapter)));
        assertThat($config->extract, identicalTo($extract));
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
     * @expectedExceptionMessage Should be array is extract.
     */
    public function validate_no_array_in_extract()
    {
        $cfg = array(
            'pdo' => $this->pdo,
            'extract' => new \stdClass(),
        );

        new Config($cfg, __FILE__);
    }
}
