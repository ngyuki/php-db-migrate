<?php
return array(
    function (\ngyuki\DbMigrate\Migrate\MigrateContext $context) {
        $context->getAdapter()->exec("insert into tt values (3000)");
    },
    function (\ngyuki\DbMigrate\Migrate\MigrateContext $context) {
        $context->getAdapter()->exec("delete from tt where id = 3000");
    },
);
