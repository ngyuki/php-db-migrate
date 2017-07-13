<?php
namespace ngyuki\DbMigrate\Migrate;

class Manager
{
    /**
     * @var DbTable
     */
    private $table;

    /**
     * @var string
     */
    private $scriptDirectory;

    /**
     * @var boolean
     */
    private $dryRun = false;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Executor
     */
    private $executor;

    /**
     * @var array
     */
    private $scriptFiles = null;

    /**
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(Config $config, Logger $logger)
    {
        $this->logger = $logger;
        $this->scriptDirectory = $config->scriptDirectory;
        $this->dryRun = $config->dryRun;
        $this->table = new DbTable($config);
        $this->executor = new Executor($config, $logger);
    }

    /**
     * @param $dir
     * @return $this
     */
    public function setScriptDirectory($dir)
    {
        $this->scriptFiles = null;
        $this->scriptDirectory = $dir;
        return $this;
    }

    /**
     * 指定バージョンを適用済を記録
     *
     * @param string $version
     * @return string
     * @throws \RuntimeException
     */
    public function fixVersion($version)
    {
        $list = $this->listScripts();

        if (array_search($version, $list, true) === false) {
            throw new \RuntimeException("version notfound: $version");
        }

        if ($this->dryRun == false) {
            $this->table->fixVersion($version);
        }

        $this->logger->log("fix version: $version");

        return $version;
    }

    /**
     * すべてのバージョンを適用済と記録
     *
     * @return string
     */
    public function fixAllVersions()
    {
        $list = $this->listScripts();

        foreach ($list as $version) {
            if ($this->table->isApplied($version) == false) {
                if ($this->dryRun == false) {
                    $this->table->fixVersion($version);
                }

                $this->logger->log("fix version: $version");
            }
        }

        $latest = end($list);

        return $latest;
    }

    /**
     * すべてのバージョンを未適用と記録
     */
    public function clearVersion()
    {
        if ($this->dryRun == false) {
            $this->table->dropTable();
        }

        $this->logger->log("clear all version");
    }

    /**
     * @return array
     */
    private function listScripts()
    {
        if ($this->scriptFiles === null) {
            $it = new \DirectoryIterator($this->scriptDirectory);

            $list = array();

            /* @var $file \SplFileInfo */
            foreach ($it as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $fn = $file->getFilename();

                if (preg_match("/^[-._a-zA-Z0-9]+$/", $fn) === 0) {
                    continue;
                }

                $list[] = $fn;
            }

            sort($list);

            $this->scriptFiles = $list;
        }

        return $this->scriptFiles;
    }

    /**
     * 未適用のマイグレーションにフィルタする
     *
     * @param array $list
     * @return array
     */
    private function filterNotApplied(array $list)
    {
        $retval = array();

        foreach ($list as $version) {
            if ($this->table->isApplied($version) == false) {
                $retval[] = $version;
            }
        }

        return $retval;
    }

    /**
     * マイグレーションの適用
     *
     * @param string $version
     */
    private function applyMigrate($version)
    {
        $fn = $this->scriptDirectory . DIRECTORY_SEPARATOR . $version;
        $this->executor->execute($fn);
    }

    /**
     * マイグレーション
     */
    public function migrate($execOnly = false)
    {
        // バージョンの一覧
        $list = $this->listScripts();

        // 最新バージョン
        $latest = end($list);

        if ($execOnly == false) {
            // 未適用のマイグレーションにフィルタ
            $list = $this->filterNotApplied($list);
        }

        if (count($list) == 0) {
            $this->logger->log("migrate nothing ... latest version: $latest");
        } else {
            foreach ($list as $version) {
                $this->logger->log("migrate: $version");
                $this->applyMigrate($version);

                if ($execOnly == false) {
                    $this->fixVersion($version);
                }
            }
        }
    }

    /**
     * マイグレーションの状態を表示
     */
    public function showStatus()
    {
        // マイグレーションの一覧
        $list = $this->listScripts();

        if (count($list) == 0) {
            $this->logger->log("migrate nothing");
            return 0;
        } else {
            $code = 0;

            foreach ($list as $version) {
                if ($this->table->isApplied($version)) {
                    $this->logger->log("* $version");
                } else {
                    $this->logger->log("  $version");
                    $code = 1;
                }
            }

            return $code;
        }
    }
}
