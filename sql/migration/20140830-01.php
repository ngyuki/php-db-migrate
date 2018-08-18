<?php

use ngyuki\DbMigrate\Migrate\MigrationContext;

return array(
    function (MigrationContext $context) {
        $val = $context['app_value'];
        if ($context->dryRun) {
            $context->exec("insert into tt values ($val) -- dry-run");
        } else {
            $context->exec("insert into tt values ($val)");
        }
    },
    function (MigrationContext $context) {
        $val = $context['app_value'];
        if ($context->dryRun) {
            $context->exec("delete from tt where id = $val -- dry-run");
        } else {
            $context->exec("delete from tt where id = $val");
        }
    },
);
