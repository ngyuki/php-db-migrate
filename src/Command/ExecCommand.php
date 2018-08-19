<?php
namespace ngyuki\DbMigrate\Command;

use ngyuki\DbMigrate\Migrate\Migration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ExecCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('exec')->setDescription('Execute scripts')
            ->addArgument('directory', InputArgument::REQUIRED, 'Sql scripts directory.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $scripts = $this->locator->collector->listScripts($directory);

        foreach ($scripts as $name => $script) {
            $migration = (new Migration())->setScript($script);
            $this->locator->logger->info("exec: $name");
            $this->locator->executor->up($name, $migration->getContent());
        }
    }
}
