<?php
use ngyuki\DbMigrate\Migrate\MigrateContext;

return array(
    function (MigrateContext $context) {
        $context->getAdapter()->exec("insert into tt values (3000)");
    },
    function (MigrateContext $context) {
        $context->getAdapter()->exec("delete from tt where id = 3000");
    },
);
