<?php
namespace ngyuki\DbMigrate\Migrate;

use ngyuki\DbMigrate\Adapter\AdapterInterface;
use ngyuki\DbMigrate\Executor\ExecutorManager;

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
     * @var MigrationCollector
     */
    private $collector;

    public function __construct(
        Logger $logger,
        AdapterInterface $adapter,
        ExecutorManager $executor,
        MigrationCollector $collector
    ) {
        $this->logger = $logger;
        $this->adapter = $adapter;
        $this->executor = $executor;
        $this->collector = $collector;
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
        $migrations = $this->collector->listStatuses();

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
        $migrations = $this->collector->listStatuses();

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
        $migrations = $this->collector->listStatuses();

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
        $scripts = $this->collector->listScripts($directory);

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
    public function markVersion($version)
    {
        $statuses = $this->collector->listStatuses();

        if (array_key_exists($version, $statuses) === false) {
            throw new \RuntimeException("version not found: $version");
        }

        $status = $statuses[$version];

        if ($status->isApplied()) {
            $this->logger->log("version already migrated: $version");
        } elseif ($status->isMissing()) {
            $this->logger->log("mark version: $version is missing");
        } else {
            $this->adapter->save($version, $status->getContent());
            $this->logger->log("mark version: $version");
        }
    }

    public function markAllVersions()
    {
        $statuses = $this->collector->listStatuses();

        foreach ($statuses as $version => $status) {
            if ($status->isApplied()) {
                // skip
            } elseif ($status->isMissing()) {
                // skip
            } else {
                $this->adapter->save($version, $status->getContent());
                $this->logger->log("mark version: $version");
            }
        }
    }

    /**
     * @param string $version
     */
    public function unmarkVersion($version)
    {
        $statuses = $this->collector->listStatuses();

        if (array_key_exists($version, $statuses) === false) {
            throw new \RuntimeException("version not found: $version");
        }

        $status = $statuses[$version];

        if ($status->isApplied() == false) {
            $this->logger->log("version not migrated: $version");
        } else {
            $this->adapter->delete($version);
            $this->logger->log("unmark version: $version");
        }
    }

    public function unmarkAllVersions()
    {
        $statuses = $this->collector->listStatuses();

        foreach ($statuses as $version => $status) {
            if ($status->isApplied()) {
                $this->adapter->delete($version);
                $this->logger->log("unmark version: $version");
            }
        }
    }

    public function clear()
    {
        $this->adapter->clear();
        $this->logger->log("clear database");
    }
}
