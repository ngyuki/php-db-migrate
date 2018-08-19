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

    public function __construct(Logger $logger, AdapterInterface $adapter, ExecutorManager $executor)
    {
        $this->logger = $logger;
        $this->adapter = $adapter;
        $this->executor = $executor;
    }

    /**
     * @param Migration[] $migrations
     * @param Migration[] $up
     * @param Migration[] $down
     */
    public function doMigrate(array $migrations, array $up, array $down)
    {
        krsort($down);
        ksort($up);

        foreach ($down as $version => $migration) {
            if ($migration->hasContent()) {
                $this->logger->info("down: $version");
                $this->executor->down($version, $migration->getContent());
                $this->adapter->delete($version);
            } else {
                $this->logger->info("unable down: $version (missing)");
            }
        }

        foreach ($up as $version => $migration) {
            if ($migration->hasContent()) {
                $this->logger->info("up: $version");
                $this->executor->up($version, $migration->getContent());
                $this->adapter->save($version, $migration->getContent());
            } else {
                $this->logger->info("unable up: $version (missing)");
            }
        }

        if (count($up) === 0 && count($down) === 0) {
            $latest = '(none)';

            foreach ($migrations as $version => $migration) {
                if ($migration->isApplied()) {
                    $latest = $version;
                }
            }

            $this->logger->info("migrate nothing ... latest version: $latest");
        }
    }
}
