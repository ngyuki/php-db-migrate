<?php
namespace ngyuki\DbMigrate\Adapter;

use ngyuki\DbMigrate\Migrate\Logger;
use PDO;
use RuntimeException;

class AdapterFactory
{
    /**
     * @param PDO $pdo
     * @param Logger $logger
     * @param bool $dryRun
     * @return AdapterInterface
     */
    public function create(PDO $pdo, Logger $logger, $dryRun)
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            return new PdoMySqlAdapter($pdo, $logger, $dryRun);
        }

        throw new RuntimeException("PDO($driver) does not support");
    }
}
