<?php
namespace ngyuki\DbMigrate\Console;

use ngyuki\DbMigrate\Migrate\Config;

class ConfigLoader
{
    public function load($path)
    {
        if (strlen($path) === 0) {
            $arr = (new Configure())->get();
            if ($arr) {
                return new Config($arr);
            }
        }

        $file = $this->resolve($path);
        $arr = $this->loadFile($file);

        if (!is_array($arr)) {
            throw new \RuntimeException('Should return array from config file.');
        }

        return new Config($arr, $file);
    }

    private function loadFile($file)
    {
        /** @noinspection PhpIncludeInspection */
        return include $file;
    }

    private function resolve($path)
    {
        if (strlen($path) === 0) {
            $path = getenv('PHP_DB_MIGRATE_CONFIG');
        }

        if (is_file($path)) {
            return realpath($path);
        }

        $files = array(
            'db-migrate.config.php',
            'db-migrate.config.php.dist',
        );

        if (strlen($path) === 0) {
            $path = getcwd();
            $files = array(
                'sql/db-migrate.config.php',
                'sql/db-migrate.config.php.dist',
                'db-migrate.config.php',
                'db-migrate.config.php.dist',
            );
        }

        if (is_dir($path) === false) {
            throw new \RuntimeException("Unable resolve config from \"$path\".");
        }

        foreach ($files as $file) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $file)) {
                return realpath($path . DIRECTORY_SEPARATOR . $file);
            }
        }

        throw new \RuntimeException(
            "Unable resolve config." . PHP_EOL .
            "default name is \"db-migrate.config.php\" or \"db-migrate.config.php.dist\" in \"$path\"."
        );
    }
}
