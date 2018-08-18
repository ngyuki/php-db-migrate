<?php
namespace ngyuki\DbMigrate\Migrate;

use ngyuki\DbMigrate\Adapter\AdapterFactory;
use ngyuki\DbMigrate\Adapter\AdapterInterface;
use ngyuki\DbMigrate\Executor\ExecutorManager;
use ngyuki\DbMigrate\Executor\PhpExecutor;
use ngyuki\DbMigrate\Executor\SqlExecutor;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function __construct(OutputInterface $output, Config $config, $dryRun)
    {
        $logger = new Logger($output);

        $adapter = (new AdapterFactory())->create($config->pdo, $logger, $dryRun, $config->migrationTable);

        $context = new MigrationContext($config->config, $logger, $adapter, $dryRun);

        $executor = new ExecutorManager($config->workingDirectory);
        $executor->add('.php', new PhpExecutor($context));
        $executor->add('.sql', new SqlExecutor($adapter));

        $collector = new MigrationCollector($adapter, $config->scriptDirectory);

        $reporter = new StatusReporter($output, $collector);

        $migrator = new Migrator($logger, $adapter, $executor);

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
