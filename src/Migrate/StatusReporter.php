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
        $statuses = $this->collector->listStatuses();

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
}
