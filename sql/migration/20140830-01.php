<?php

use ngyuki\DbMigrate\Migrate\MigrateContext;

return array(
    function (MigrateContext $context) {
        if ($context->isDryRun()) {
            $context->getAdapter()->exec("insert into tt values (3000) -- dry-run");
        } else {
            $context->getAdapter()->exec("insert into tt values (3000)");
        }
    },
    function (MigrateContext $context) {
        if ($context->isDryRun()) {
            $context->getAdapter()->exec("delete from tt where id = 3000 -- dry-run");
        } else {
            $context->getAdapter()->exec("delete from tt where id = 3000");
        }
    },
);
