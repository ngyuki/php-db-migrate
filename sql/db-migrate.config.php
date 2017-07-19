<?php
$host   = getenv('MYSQL_HOST')     ?: 'localhost';
$user   = getenv('MYSQL_USER')     ?: 'root';
$pass   = getenv('MYSQL_PASSWORD') ?: '';
$dbname = getenv('MYSQL_DATABASE') ?: 'test';

$pdo = new \PDO("mysql:dbname=$dbname;host=$host;charset=utf8", $user, $pass);

return array(
    'pdo' => $pdo,
    'directory' => 'migrate',
    'args' => array($pdo),
);
