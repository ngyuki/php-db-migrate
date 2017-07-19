<?php
namespace ngyuki\DbMigrate\Executor;

interface ExecutorInterface
{
    public function up($filename);

    public function down($filename);
}
