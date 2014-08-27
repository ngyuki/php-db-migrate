<?php
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Test\\', __DIR__);
$loader->add('TestHelper\\', __DIR__);

require_once 'PHPUnit/Framework/Assert/Functions.php';

return $loader;
