<?php

use ngyuki\DbMigrate\Migrate\MigrateContext;

return array(
    function (MigrateContext $context) {
        $val = $context->get('app_value');
        if ($context->isDryRun()) {
            $context->getAdapter()->exec("insert into tt values ($val) -- dry-run");
        } else {
            $context->getAdapter()->exec("insert into tt values ($val)");
        }
    },
    function (MigrateContext $context) {
        $val = $context->get('app_value');
        if ($context->isDryRun()) {
            $context->getAdapter()->exec("delete from tt where id = $val -- dry-run");
        } else {
            $context->getAdapter()->exec("delete from tt where id = $val");
        }
    },
);
