<?php

use ngyuki\DbMigrate\Migrate\MigrationContext;

return array(
    function (MigrationContext $context, $app_value) {
        echo 'running php script';
        if ($context->dryRun) {
            $context->exec("insert into tt values ($app_value) -- dry-run");
        } else {
            $context->exec("insert into tt values ($app_value)");
        }
        return [
            "insert into tt values ($app_value + 1)",
        ];
    },
    function (MigrationContext $context, $app_value) {
        if ($context->dryRun) {
            $context->exec("delete from tt where id = $app_value -- dry-run");
        } else {
            $context->exec("delete from tt where id = $app_value");
        }
        return [
            "delete from tt where id = $app_value + 1",
        ];
    },
);
