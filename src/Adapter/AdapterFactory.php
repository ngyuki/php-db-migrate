<?php
namespace ngyuki\DbMigrate\Adapter;

use ngyuki\DbMigrate\Driver\DriverFactory;
use ngyuki\DbMigrate\Migrate\Logger;
use PDO;

class AdapterFactory
{
    /**
     * @param PDO $pdo
     * @param Logger $logger
     * @param bool $dryRun
     * @param string $table
     *
     * @return AdapterInterface
     */
    public function create(PDO $pdo, Logger $logger, $dryRun, $table)
    {
        $driver = (new DriverFactory())->create($pdo);
        return new Adapter($driver, $logger, $dryRun, $table);
    }
}
