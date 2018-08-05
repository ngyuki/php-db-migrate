<?php
namespace ngyuki\DbMigrate\Console;

use ngyuki\DbMigrate\Migrate\Config;

class ConfigLoader
{
    /**
     * @param string|null $path
     * @return Config
     */
    public function load(string $path = null): Config
    {
        $path = $this->resolve($path);
        return $this->loadFile($path);
    }

    private function resolve(string $path = null)
    {
        if (strlen((string)$path)) {
            return $path;
        }

        $path = getenv('PHP_DB_MIGRATE_CONFIG');
        if (strlen((string)$path)) {
            return $path;
        }

        $path = $this->resolveComposer();
        if (strlen((string)$path)) {
            return $path;
        }

        $path = $this->resolveCurrentDirectory();
        if (strlen((string)$path)) {
            return $path;
        }

        throw new \RuntimeException(
            'Unable resolve config file.' . PHP_EOL . PHP_EOL .
            '1. "-c|--config" option' . PHP_EOL .
            '2. "PHP_DB_MIGRATE_CONFIG" environment' . PHP_EOL .
            '3. "extra.db-migrate" in composer.json ' . PHP_EOL .
            '4. "db-migrate.php" or "db-migrate.php.dist" in PWD'
        );
    }

    private function mustArray($arr): array
    {
        if (!is_array($arr)) {
            throw new \RuntimeException('Should return array from config file.');
        }
        return $arr;
    }

    private function loadFile(string $path): Config
    {
        if (!is_file($path)) {
            throw new \RuntimeException("Unable resolve config from \"$path\".");
        }

        $path = realpath($path);
        if ($path === false) {
            throw new \RuntimeException("Unable resolve config from \"$path\".");
        }

        /** @noinspection PhpIncludeInspection */
        $arr = include $path;
        $arr = $this->mustArray($arr);
        return new Config($arr, $path);
    }

    private function resolveComposer()
    {
        $composerFile = $this->findUpComposer();
        if ($composerFile === null) {
            return null;
        }

        $composerDir = dirname($composerFile);

        $composerContent = file_get_contents($composerFile);
        if ($composerContent === false) {
            throw new \RuntimeException("Unable read \"$composerFile\"");
        }

        $composerConfig = json_decode($composerContent, true);
        if ($composerConfig === false) {
            throw new \RuntimeException("Unable json decode \"$composerFile\"");
        }

        if (!isset($composerConfig['extra']['db-migrate'])) {
            return null;
        }

        $list = (array)$composerConfig['extra']['db-migrate'];

        foreach ($list as $name) {
            $path = $composerDir . DIRECTORY_SEPARATOR . $name;
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new \RuntimeException("Unable resolve from \"$composerFile\".");
    }

    private function findUpComposer()
    {
        $dir = getcwd();
        for (;;) {
            $path = $dir . DIRECTORY_SEPARATOR . 'composer.json';
            if (file_exists($path)) {
                return $path;
            }
            $next = dirname($dir);
            if ($next === $dir) {
                break;
            }
            $dir = $next;
        }
        return null;
    }

    private function resolveCurrentDirectory()
    {
        $dir = getcwd();
        $list = array(
            'db-migrate.php',
            'db-migrate.php.dist',
        );
        foreach ($list as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }
}
