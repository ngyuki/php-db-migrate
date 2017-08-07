<?php
namespace ngyuki\DbMigrate\Adapter;

use PDO;
use RuntimeException;

class AdapterFactory
{
    /**
     * @param PDO $pdo
     * @return AdapterInterface
     */
    public function create(PDO $pdo)
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            return new PdoMySqlAdapter($pdo);
        }

        throw new RuntimeException("PDO($driver) does not support");
    }
}
