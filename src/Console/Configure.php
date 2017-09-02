<?php
namespace ngyuki\DbMigrate\Console;

class Configure
{
    private static $callback;

    public static function register($callback)
    {
        self::$callback = $callback;
    }

    public function get()
    {
        if (self::$callback) {
            $callback = self::$callback;
            return $callback();
        } else {
            return null;
        }
    }
}
