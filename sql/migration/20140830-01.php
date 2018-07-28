<?php

use ngyuki\DbMigrate\Migrate\MigrateContext;

return array(
    function (MigrateContext $context) {
        $val = $context->get('app_value');
        if ($context->dryRun) {
            $context->exec("insert into tt values ($val) -- dry-run");
        } else {
            $context->exec("insert into tt values ($val)");
        }
    },
    function (MigrateContext $context) {
        $val = $context->get('app_value');
        if ($context->dryRun) {
            $context->exec("delete from tt where id = $val -- dry-run");
        } else {
            $context->exec("delete from tt where id = $val");
        }
    },
);
