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
     * @param Logger $logger
     * @param Config $config
     * @param bool $dryRun
     * @return Migrator
     */
    public static function create(Logger $logger, Config $config, $dryRun)
    {
        $adapter = (new AdapterFactory())->create($config->pdo, $logger, $dryRun);

        $context = new MigrateContext($config, $logger, $adapter);

        $executor = new ExecutorManager($config->workingDirectory);
        $executor->add('.php', new PhpExecutor($context));
        $executor->add('.sql', new SqlExecutor($adapter));

        return new Migrator($logger, $adapter, $executor, $config->scriptDirectory);
    }

    public function __construct(Logger $logger, AdapterInterface $adapter, ExecutorManager $executor, $scriptDirectory)
    {
        $this->logger = $logger;
        $this->adapter = $adapter;
        $this->executor = $executor;
        $this->scriptDirectory = $scriptDirectory;
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

        foreach ($versions as $version => $row) {
            if (array_key_exists($version, $statuses) === false) {
                $statuses[$version] = new Status($version);
            }

            $statuses[$version]->setApplied(true);
            $statuses[$version]->setContent($row['content']);
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
            if ($status->isMissing()) {
                $suffix = " (missing)";
            } else {
                $suffix = "";
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
            if ($migration->hasContent()) {
                $this->logger->log("down: $version");
                $this->executor->down($version, $migration->getContent());
                $this->adapter->delete($version);
            } else {
                $this->logger->log("unable down: $version (missing)");
            }
        }

        foreach ($up as $version => $migration) {
            if ($migration->hasContent()) {
                $this->logger->log("up: $version");
                $this->executor->up($version, $migration->getContent());
                $this->adapter->save($version, $migration->getContent());
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

            if ($migration->isMissing() === null) {
                // ファイルが見つからなければ DOWN する
                $cmp = 1;
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
            $migration = new Status($name);
            $migration->setScript($script);
            $this->logger->log("exec: $name");
            $this->executor->up($name, $migration->getContent());
        }
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $statuses = $this->getStatuses();

        if (array_key_exists($version, $statuses) === false) {
            throw new \RuntimeException("version not found: $version");
        }

        $status = $statuses[$version];

        if ($status->isApplied()) {
            $this->logger->log("version already migrated: $version");
        } elseif ($status->hasContent() == false) {
            $this->logger->log("set version: $version is missing");
        } else {
            $this->adapter->save($version, $status->getContent());
            $this->logger->log("set version: $version");
        }
    }

    public function setAllVersions()
    {
        $statuses = $this->getStatuses();

        foreach ($statuses as $version => $status) {
            if ($status->isApplied()) {
                // skip
            } elseif ($status->hasContent() == false) {
                $this->logger->log("set version: $version is missing");
            } else {
                $this->adapter->save($version, $status->getContent());
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
            throw new \RuntimeException("version not found: $version");
        }

        $status = $statuses[$version];

        if ($status->isApplied() == false) {
            $this->logger->log("version not migrated: $version");
        } else {
            $this->adapter->delete($version);
            $this->logger->log("unset version: $version");
        }
    }

    public function unsetAllVersions()
    {
        $statuses = $this->getStatuses();

        foreach ($statuses as $version => $status) {
            if ($status->isApplied()) {
                $this->adapter->delete($version);
                $this->logger->log("unset version: $version");
            }
        }
    }

    public function clear()
    {
        $this->adapter->clear();
        $this->logger->log("clear database");
    }
}
