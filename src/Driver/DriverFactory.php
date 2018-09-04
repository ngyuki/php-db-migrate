<?php
namespace ngyuki\DbMigrate\Driver;

use PDO;
use RuntimeException;

class DriverFactory
{
    /**
     * @param PDO $pdo
     *
     * @return DriverInterface
     */
    public function create(PDO $pdo)
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            return new PdoMySqlDriver($pdo);
        }
        throw new RuntimeException("PDO($driver) does not support");
    }
}
