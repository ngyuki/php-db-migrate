<?php
namespace ngyuki\DbMigrate\Migrate;

use ngyuki\DbMigrate\Adapter\AdapterFactory;
use ngyuki\DbMigrate\Adapter\AdapterInterface;
use ngyuki\DbMigrate\Executor\ExecutorManager;
use ngyuki\DbMigrate\Executor\PhpExecutor;
use ngyuki\DbMigrate\Executor\SqlExecutor;
use Symfony\Component\Console\Exception\LogicException;

/**
 * @property Logger             $logger
 * @property AdapterInterface   $adapter
 * @property ExecutorManager    $executor
 * @property MigrationCollector $collector
 * @property StatusReporter     $reporter
 * @property Migrator           $migrator
 */
class ServiceLocator
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

    /**
     * @var StatusReporter
     */
    private $reporter;

    /**
     * @var Migrator
     */
    private $migrator;

    public function __construct(Logger $logger, Config $config, $dryRun)
    {
        $adapter = (new AdapterFactory())->create($config->pdo, $logger, $dryRun);

        $context = new MigrateContext($config, $logger, $adapter, $dryRun);

        $executor = new ExecutorManager($config->workingDirectory);
        $executor->add('.php', new PhpExecutor($context));
        $executor->add('.sql', new SqlExecutor($adapter));

        $collector = new MigrationCollector($adapter, $config->scriptDirectory);

        $reporter = new StatusReporter($logger, $collector);

        $migrator = new Migrator($logger, $adapter, $executor, $collector, $reporter);

        $this->logger = $logger;
        $this->adapter = $adapter;
        $this->executor = $executor;
        $this->collector = $collector;
        $this->reporter = $reporter;
        $this->migrator = $migrator;
    }

    public function __get($name)
    {
        if (!isset($this->$name)) {
            throw new LogicException("service $name is not defined");
        }
        return $this->$name;
    }
}
