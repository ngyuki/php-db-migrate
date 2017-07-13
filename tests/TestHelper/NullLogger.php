<?php
namespace TestHelper;

use ngyuki\DbMigrate\Migrate\Logger;

class NullLogger extends Logger
{
    public function __construct()
    {
    }

    public function log($log)
    {
    }

    public function verbose($log)
    {
    }
}
