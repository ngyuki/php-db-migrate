<?php
namespace ngyuki\DbMigrate\Migrate;

use PDO;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @property PDO      $pdo
 * @property string   $scriptDirectory
 * @property string   $workingDirectory
 */
class Config extends \ArrayIterator
{
    /**
     * @param array $cfg
     * @param string $file
     */
    public function __construct(array $cfg, $file = '')
    {
        $cfg += array(
            'pdo' => null,
            'directory' => 'migrate',
            'work_dir' => '',
        );

        parent::__construct($cfg, self::ARRAY_AS_PROPS);

        $this->scriptDirectory = rtrim($this['directory'], DIRECTORY_SEPARATOR);

        if (strlen($this['work_dir'])) {
            $this->workingDirectory = $this['work_dir'];
        } elseif (strlen($file)) {
            $this->workingDirectory = dirname($file);
        } else {
            $this->workingDirectory = getcwd();
        }

        $this->fixRelativePath();
        $this->validate();
    }

    public function toArray()
    {
        return $this->getArrayCopy();
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
}
