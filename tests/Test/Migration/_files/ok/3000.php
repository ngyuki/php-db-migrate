<?php
use ngyuki\DbMigrate\Migrate\MigrationContext;

return array(
    function (MigrationContext $context) {
        $context->exec("insert into tt values (3000)");
    },
    function (MigrationContext $context) {
        $context->exec("delete from tt where id = 3000");
    },
);
