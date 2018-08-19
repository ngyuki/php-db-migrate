<?php

use ngyuki\DbMigrate\Migrate\MigrationContext;

return array(
    function (MigrationContext $context, $app_value) {
        echo 'running php script';
        if ($context->isDryRun()) {
            $context->exec("insert into tt values (?) -- dry-run", [$app_value]);
        } else {
            $context->exec("insert into tt values (?)", [$app_value]);
        }
        return [
            ["insert into tt values (?)", [$app_value + 1]],
        ];
    },
    function (MigrationContext $context, $app_value) {
        if ($context->isDryRun()) {
            $context->exec("delete from tt where id = ? -- dry-run", [$app_value]);
        } else {
            $context->exec("delete from tt where id = ?", [$app_value]);
        }
        return [
            ["delete from tt where id = ?", [$app_value + 1]],
        ];
    },
);
