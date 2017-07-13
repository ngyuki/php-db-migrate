<?php
namespace ngyuki\DbMigrate\Migrate;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

class DbTable
{
    const TABLE_NAME = 'db_migrate';
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $doctrine;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->doctrine = $config->adapter->getDoctrine();
    }

    /**
     * @return bool
     */
    public function isExistTable()
    {
        return $this->doctrine->getSchemaManager()->tablesExist(array(self::TABLE_NAME));
    }

    public function createTable()
    {
        if ($this->isExistTable()) {
            return;
        }

        $table = new Table(self::TABLE_NAME);

        $table->addColumn('version', Type::STRING, array(
            'length' => 255,
            'notnull' => true,
            'default' => '',
        ));

        $table->addColumn('apply_at', Type::DATETIME, array(
            'notnull' => false,
            'default' => null,
        ));

        $table->setPrimaryKey(array('version'));

        $this->doctrine->getSchemaManager()->createTable($table);
    }

    public function dropTable()
    {
        if ($this->isExistTable()) {
            $this->doctrine->getSchemaManager()->dropTable(self::TABLE_NAME);
        }
    }

    /**
     * @param string $version
     * @param \DateTime $apply_at
     * @throws \Exception
     */
    public function fixVersion($version, \DateTime $apply_at = null)
    {
        if ($apply_at === null) {
            $apply_at = new \DateTime();
        }

        $this->createTable();

        $this->doctrine->beginTransaction();

        try {
            $this->doctrine->delete(self::TABLE_NAME, array(
                'version' => $version,
            ));

            $this->doctrine->insert(self::TABLE_NAME, array(
                'version' => $version,
                'apply_at' => $apply_at->format('Y-m-d\TH:i:s'),
            ));

            $this->doctrine->commit();
        } catch (\Exception $ex) {
            $this->doctrine->rollBack();
            throw $ex;
        }
    }

    /**
     * @return array
     */
    public function getAppliedList()
    {
        /* @var $stmt Statement*/
        $stmt = $this->doctrine->createQueryBuilder()
            ->select('version')
            ->from(self::TABLE_NAME, 'M')
            ->orderBy('version')
            ->execute()
        ;

        return $stmt->fetchAll();
    }

    /**
     * @param string $version
     * @return boolean
     */
    public function isApplied($version)
    {
        if ($this->isExistTable() == false) {
            return false;
        }

        /* @var $stmt Statement*/
        $stmt = $this->doctrine->createQueryBuilder()
            ->select('version')
            ->from(self::TABLE_NAME, 'M')
            ->where('version = :version')
            ->setParameter('version', $version)
            ->execute()
        ;

        return !!$stmt->fetch();
    }
}
