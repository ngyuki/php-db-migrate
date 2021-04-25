<?php
namespace ngyuki\DbMigrate\Migrate;

use Symfony\Component\Console\Output\OutputInterface;

class StatusReporter
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var MigrationCollector
     */
    private $collector;

    public function __construct(OutputInterface $output, MigrationCollector $collector)
    {
        $this->output = $output;
        $this->collector = $collector;
    }

    /**
     * マイグレーションの状態を表示
     */
    public function show(bool $missing)
    {
        $migrations = $this->collector->listMigrations();

        if (count($migrations) == 0) {
            $this->output->writeln("<info>migrate nothing</info>");
            return 0;
        }

        $code = 0;

        foreach ($migrations as $version => $migration) {
            $line = '';

            if ($migration->isApplied()) {
                $line .= "<info>[*]</info> ";
            } else {
                $line .= "<info>[ ]</info> ";
                $code = 1;
            }

            $line .= "<comment>{$version}</comment>";

            if ($migration->isMissing()) {
                $line .= " <fg=red;options=bold>(missing)</>";
                if ($missing) {
                    $code = 1;
                }
            }

            $this->output->writeln($line);
        }

        return $code;
    }
}
