<?php
\ngyuki\DbMigrate\Console\Configure::register(function () {
    if (file_exists(__DIR__ . '/config.php')) {
        /** @noinspection PhpIncludeInspection */
        return require __DIR__ . '/config.php';
    }
    return require __DIR__ . '/config.php.dist';
});
