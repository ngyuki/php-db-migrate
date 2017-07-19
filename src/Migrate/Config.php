<?php
namespace ngyuki\DbMigrate\Migrate;

use PDO;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @property PDO      $pdo
 * @property array    $args
 * @property string   $scriptDirectory
 * @property string   $workingDirectory
 * @property boolean  $dryRun
 */
class Config
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var string
     */
    protected $scriptDirectory;

    /**
     * @var string
     */
    protected $workingDirectory;

    /**
     * @var boolean
     */
    protected $dryRun;

    /**
     * @param array  $cfg
     * @param string $fn
     */
    public function __construct(array $cfg, $fn)
    {
        $cfg += array(
            'pdo' => null,
            'args' => array(),
            'directory' => 'migrate',
        );

        $this->pdo = $cfg['pdo'];

        $this->args = $cfg['args'];
        $this->scriptDirectory = rtrim($cfg['directory'], DIRECTORY_SEPARATOR);

        if ($fn === null) {
            $this->workingDirectory = getcwd();
        } else {
            $this->workingDirectory = dirname($fn);
        }

        $this->fixRelativePath();
        $this->validate();
    }

    protected function fixRelativePath()
    {
        $fs = new Filesystem();

        if (!$fs->isAbsolutePath($this->scriptDirectory)) {
            $this->scriptDirectory =
                $this->workingDirectory . DIRECTORY_SEPARATOR . $this->scriptDirectory;
        }
    }

    protected function validate()
    {
        if ($this->pdo instanceof \PDO === false) {
            throw new \RuntimeException('Should be PDO is pdo.');
        }

        if (!is_array($this->args)) {
            throw new \RuntimeException('Should be array is args.');
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name) == false) {
            throw new \LogicException("undefined property $name");
        } else {
            return $this->$name;
        }
    }

    public function __set($name, $value)
    {
        if (property_exists($this, $name) == false) {
            throw new \LogicException("undefined property $name");
        } else {
            $this->$name = $value;
        }
    }
}
