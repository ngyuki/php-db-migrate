<?php
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Test\\', __DIR__);
$loader->add('TestHelper\\', __DIR__);

$reflection = new ReflectionClass('PHPUnit_Framework_Assert');
require_once dirname($reflection->getFileName()) . '/Assert/Functions.php';
