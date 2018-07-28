<?php
namespace ngyuki\DbMigrate\Migrate;

class StatusReporter
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var MigrationCollector
     */
    private $collector;

    public function __construct(Logger $logger, MigrationCollector $collector)
    {
        $this->logger = $logger;
        $this->collector = $collector;
    }

    /**
     * マイグレーションの状態を表示
     */
    public function show()
    {
        $migrations = $this->collector->listMigrations();

        if (count($migrations) == 0) {
            $this->logger->log("migrate nothing");
            return 0;
        }

        $code = 0;

        foreach ($migrations as $version => $migration) {
            if ($migration->isMissing()) {
                $suffix = " (missing)";
            } else {
                $suffix = "";
            }

            if ($migration->isApplied()) {
                $this->logger->log("* {$version}{$suffix}");
            } else {
                $this->logger->log("  {$version}{$suffix}");
                $code = 1;
            }
        }

        return $code;
    }
}
