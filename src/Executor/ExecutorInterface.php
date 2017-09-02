<?php
namespace ngyuki\DbMigrate\Executor;

interface ExecutorInterface
{
    public function up($content);

    public function down($content);
}
