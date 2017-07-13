<?php
namespace ngyuki\DbMigrate\Migrate;

class ConfigLoader
{
    public function load($fn)
    {
        $arr = include $fn;

        if (!is_array($arr)) {
            throw new \RuntimeException('Should return array from config file.');
        }

        $fn = func_get_arg(0);

        return new Config($arr, $fn);
    }

    public function resolve($configPath)
    {
        $files = array(
            'sql/db-migrate.config.php',
            'sql/db-migrate.config.php.dist',
            'db-migrate.config.php',
            'db-migrate.config.php.dist',
        );

        if (is_file($configPath)) {
            return realpath($configPath);
        } elseif (is_dir($configPath)) {
            $files = array(
                'db-migrate.config.php',
                'db-migrate.config.php.dist',
            );

            $configPath = rtrim($configPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        foreach ($files as $file) {
            $path = $configPath . $file;

            if (file_exists($path)) {
                return realpath($path);
            }
        }

        if (strlen($configPath) === 0) {
            throw new \RuntimeException(
                "Unable resolve config." . PHP_EOL .
                "default name is \"db-migrate.config.php\" or \"db-migrate.config.php.dist\" in current directory."
            );
        } else {
            throw new \RuntimeException("Unable resolve config from \"$configPath\".");
        }
    }
}
