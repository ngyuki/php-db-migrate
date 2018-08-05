<?php
use ngyuki\DbMigrate\Migrate\MigrateContext;

return array(
    function (MigrateContext $context) {
        $context->exec("insert into tt values (3000)");
    },
    function (MigrateContext $context) {
        $context->exec("delete from tt where id = 3000");
    },
);
