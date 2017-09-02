<?php
namespace ngyuki\DbMigrate\Migrate;

use ngyuki\DbMigrate\Adapter\AdapterFactory;
use ngyuki\DbMigrate\Adapter\AdapterInterface;
use ngyuki\DbMigrate\Executor\ExecutorManager;
use ngyuki\DbMigrate\Executor\PhpExecutor;
use ngyuki\DbMigrate\Executor\SqlExecutor;

class Migrator
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var ExecutorManager
     */
    private $executor;

    /**
     * @var string
     */
    private $scriptDirectory;

    /**
     * @var boolean
     */
    private $dryRun = false;

    /**
     * @param Logger $logger
     * @param Config $config
     * @return self
     */
    public static function create(Logger $logger, Config $config)
    {
        $adapter = (new AdapterFactory())->create($config->pdo);

        $executor = new ExecutorManager($config->workingDirectory);
        $executor->add('.php', new PhpExecutor($logger, $config->args, $config->dryRun));
        $executor->add('.sql', new SqlExecutor($logger, $adapter, $config->dryRun));

        return new Migrator($logger, $adapter, $executor, $config->scriptDirectory, $config->dryRun);
    }

    public function __construct(Logger $logger, AdapterInterface $adapter, ExecutorManager $executor, $scriptDirectory, $dryRun)
    {
        $this->logger = $logger;
        $this->adapter = $adapter;
        $this->executor = $executor;
        $this->scriptDirectory = $scriptDirectory;
        $this->dryRun = $dryRun;
    }

    /**
     * @param string|null $directory
     * @return array
     */
    private function listScripts($directory = null)
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

    private function getStatuses()
    {
        $scripts = $this->listScripts();
        $versions = $this->adapter->fetchAll();

        /** @var $statuses Status[] */
        $statuses = array();

        foreach ($scripts as $version => $script) {
            $statuses[$version] = new Status($version);
            $statuses[$version]->setScript($script);
        }

        foreach ($versions as $version => $_) {
            if (array_key_exists($version, $statuses) === false) {
                $statuses[$version] = new Status($version);
            }

            $statuses[$version]->setApplied(true);
        }

        ksort($statuses);
        return $statuses;
    }

    /**
     * マイグレーションの状態を表示
     */
    public function showStatus()
    {
        $statuses = $this->getStatuses();

        if (count($statuses) == 0) {
            $this->logger->log("migrate nothing");
            return 0;
        }

        $code = 0;

        foreach ($statuses as $version => $status) {
            if ($status->hasScript()) {
                $suffix = "";
            } else {
                $suffix = " (missing)";
            }


            if ($status->isApplied()) {
                $this->logger->log("* {$version}{$suffix}");
            } else {
                $this->logger->log("  {$version}{$suffix}");
                $code = 1;
            }
        }

        return $code;
    }

    /**
     * @param Status[] $migrations
     * @param Status[] $up
     * @param Status[] $down
     */
    private function doMigrate(array $migrations, array $up, array $down)
    {
        krsort($down);
        ksort($up);

        foreach ($down as $version => $migration) {
            if ($migration->hasScript()) {
                $this->logger->log("down: $version");
                $this->executor->down($migration->getScript());
                if ($this->dryRun == false) {
                    $this->adapter->delete($version);
                }
            } else {
                $this->logger->log("unable down: $version (missing)");
            }
        }

        foreach ($up as $version => $migration) {
            if ($migration->hasScript()) {
                $this->logger->log("up: $version");
                $this->executor->up($migration->getScript());
                if ($this->dryRun == false) {
                    $this->adapter->save($version);
                }
            } else {
                $this->logger->log("unable up: $version (missing)");
            }
        }

        if (count($up) === 0 && count($down) === 0) {
            $latest = '(none)';

            foreach ($migrations as $version => $migration) {
                if ($migration->isApplied()) {
                    $latest = $version;
                }
            }

            $this->logger->log("migrate nothing ... latest version: $latest");
        }
    }

    /**
     * マイグレーション
     *
     * @param string $target このバージョンまでマイグレートする
     */
    public function migrate($target = null)
    {
        $migrations = $this->getStatuses();

        $up = array();
        $down = array();

        foreach ($migrations as $version => $migration) {
            if ($target === null) {
                // 未指定なら常に UP する
                $cmp = -1;
            } else {
                $cmp = strcmp($version, $target);
            }

            if ($cmp <= 0) {
                if (!$migration->isApplied()) {
                    $up[$version] = $migration;
                }
            } else {
                if ($migration->isApplied()) {
                    $down[$version] = $migration;
                }
            }
        }

        $this->doMigrate($migrations, $up, $down);
    }

    public function up()
    {
        $migrations = $this->getStatuses();

        $up = array();

        foreach ($migrations as $version => $migration) {
            if ($migration->isApplied() == false) {
                $up[$version] = $migration;
                break;
            }
        }

        $this->doMigrate($migrations, $up, array());
    }

    public function down()
    {
        $migrations = $this->getStatuses();

        $down = array();

        foreach ($migrations as $version => $migration) {
            if ($migration->isApplied()) {
                $down = array();
                $down[$version] = $migration;
            }
        }

        $this->doMigrate($migrations, array(), $down);
    }

    /**
     * スクリプト実行
     *
     * @param string $directory
     */
    public function exec($directory)
    {
        $scripts = $this->listScripts($directory);

        foreach ($scripts as $name => $script) {
            $this->logger->log("exec: $name");
            $this->executor->up($script);
        }
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $statuses = $this->getStatuses();

        if (array_key_exists($version, $statuses) === false) {
            throw new \RuntimeException("version notfound: $version");
        }

        $status = $statuses[$version];

        if ($status->isApplied()) {
            $this->logger->log("version already migrated: $version");
        } else {
            if ($this->dryRun == false) {
                $this->adapter->save($version);
            }
            $this->logger->log("set version: $version");
        }
    }

    public function setAllVersions()
    {
        $statuses = $this->getStatuses();

        foreach ($statuses as $version => $status) {
            if ($status->isApplied()) {
                // skip
            } else {
                if ($this->dryRun == false) {
                    $this->adapter->save($version);
                }
                $this->logger->log("set version: $version");
            }
        }
    }

    /**
     * @param string $version
     */
    public function unsetVersion($version)
    {
        $statuses = $this->getStatuses();

        if (array_key_exists($version, $statuses) === false) {
            throw new \RuntimeException("version notfound: $version");
        }

        $status = $statuses[$version];

        if ($status->isApplied() == false) {
            $this->logger->log("version not migrated: $version");
        } else {
            if ($this->dryRun == false) {
                $this->adapter->delete($version);
            }
            $this->logger->log("unset version: $version");
        }
    }

    public function unsetAllVersions()
    {
        $statuses = $this->getStatuses();

        foreach ($statuses as $version => $status) {
            if ($status->isApplied()) {
                if ($this->dryRun == false) {
                    $this->adapter->delete($version);
                }
                $this->logger->log("unset version: $version");
            }
        }
    }

    public function clear()
    {
        if ($this->dryRun) {
            $this->logger->log("clear database ... dry run");
        } else {
            $this->adapter->clear();
            $this->logger->log("clear database");
        }
    }
}
