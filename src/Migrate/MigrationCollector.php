<?php
namespace ngyuki\DbMigrate\Migrate;

use ngyuki\DbMigrate\Adapter\AdapterInterface;

class MigrationCollector
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var string
     */
    private $scriptDirectory;

    public function __construct(AdapterInterface $adapter, $scriptDirectory)
    {
        $this->adapter = $adapter;
        $this->scriptDirectory = $scriptDirectory;
    }

    /**
     * @param string|null $directory
     * @return array
     */
    public function listScripts($directory = null)
    {
        if ($directory === null) {
            $directory = $this->scriptDirectory;
        }

        $list = array();

        foreach (new \DirectoryIterator($directory) as $file) {
            /* @var $file \SplFileInfo */
            if (!$file->isFile()) {
                continue;
            }

            $fn = $file->getFilename();

            if (preg_match("/^[-._a-zA-Z0-9]+$/", $fn) === 0) {
                continue;
            }

            $list[$fn] = $file->getRealPath();
        }

        ksort($list);
        return $list;
    }

    public function listMigrations()
    {
        $scripts = $this->listScripts();
        $versions = $this->adapter->fetchAll();

        /** @var $migrations Migration[] */
        $migrations = array();

        foreach ($scripts as $version => $script) {
            $migrations[$version] = new Migration();
            $migrations[$version]->setScript($script);
        }

        foreach ($versions as $version => $row) {
            if (array_key_exists($version, $migrations) === false) {
                $migrations[$version] = new Migration();
            }

            $migrations[$version]->setApplied(true);
            $migrations[$version]->setContent($row['content']);
        }

        ksort($migrations);
        return $migrations;
    }
}
