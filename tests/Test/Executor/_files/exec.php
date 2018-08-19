<?php
use ngyuki\DbMigrate\Migrate\MigrationContext;

return array(
    function (\PDO $pdo, $ore, MigrationContext $context) {
        $context->info('ore');
        $context->exec("insert into tt values (?)", [$ore]);
        if (!$context->isDryRun()) {
            $context->verbose('pdo');
            $pdo->prepare('insert into tt values (?)')->execute([999]);
        }
        return [
            'insert into tt values (888)',
            ['insert into tt values (?)', [777]],
            ['insert into tt values (?)', [666]],
        ];
    },
    function ($ore, MigrationContext $context) {
        $context->exec("delete from tt where id = ?", [$ore]);
        $context->exec("delete from tt where id = ?", [999]);
        return 'delete from tt where id in (888, 777, 666)';
    },
);
