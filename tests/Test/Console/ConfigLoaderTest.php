<?php
namespace Test\Console;

use ngyuki\DbMigrate\Console\ConfigLoader;
use ngyuki\DbMigrate\Migrate\Config;
use TestHelper\TestCase;

class ConfigLoaderTest extends TestCase
{
    public function tearDown()
    {
        putenv("PHP_DB_MIGRATE_CONFIG=");
    }

    /**
     * @test
     */
    public function file_()
    {
        $file = __DIR__ . '/_files/db-migrate.php';
        $config = (new ConfigLoader())->load($file);
        assertThat($config, isInstanceOf(Config::class));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function file_err()
    {
        $file = __DIR__ . '/_files/xxx.php';
        (new ConfigLoader())->load($file);
    }

    /**
     * @test
     */
    public function env_()
    {
        $file = __DIR__ . '/_files/db-migrate.php';
        putenv("PHP_DB_MIGRATE_CONFIG=$file");
        $config = (new ConfigLoader())->load('');
        assertThat($config, isInstanceOf(Config::class));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function env_err()
    {
        $file = __DIR__ . '/_files/xxx.php';
        putenv("PHP_DB_MIGRATE_CONFIG=$file");
        $config = (new ConfigLoader())->load('');
        assertThat($config, isInstanceOf(Config::class));
    }

    /**
     * @test
     */
    public function composer_()
    {
        chdir(__DIR__ . '/_files/composer');

        $config = (new ConfigLoader())->load('');
        assertThat($config, isInstanceOf(Config::class));
    }

    /**
     * @test
     */
    public function composer_2nd()
    {
        chdir(__DIR__ . '/_files/composer_2nd');

        $config = (new ConfigLoader())->load('');
        assertThat($config, isInstanceOf(Config::class));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function composer_err()
    {
        chdir(__DIR__ . '/_files/composer_err');

        (new ConfigLoader())->load('');
    }

    /**
     * @test
     */
    public function cwd_()
    {
        chdir(__DIR__ . '/_files');

        $config = (new ConfigLoader())->load('');
        assertThat($config, isInstanceOf(Config::class));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function missing_()
    {
        (new ConfigLoader())->load('');
    }
}
