<?php
namespace ngyuki\DbMigrate\Migrate;

use PDO;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @property PDO      $pdo
 * @property string   $scriptDirectory
 * @property string   $workingDirectory
 */
class Config
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $scriptDirectory;

    /**
     * @var string
     */
    protected $workingDirectory;

    /**
     * @param array  $cfg
     * @param string $file
     */
    public function __construct(array $cfg, $file = '')
    {
        $cfg += array(
            'pdo' => null,
            'directory' => 'migrate',
            'work_dir' => '',
        );

        $this->pdo = $cfg['pdo'];

        $this->scriptDirectory = rtrim($cfg['directory'], DIRECTORY_SEPARATOR);

        if (strlen($cfg['work_dir'])) {
            $this->workingDirectory = $cfg['work_dir'];
        } elseif (strlen($file)) {
            $this->workingDirectory = dirname($file);
        } else {
            $this->workingDirectory = getcwd();
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
