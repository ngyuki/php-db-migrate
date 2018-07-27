<?php
$env = new \TestHelper\TestEnv();
$pdo = $env->pdo();

return array(
    'pdo' => $pdo,
    'args' => array($pdo),
    'directory' => 'migrations',
);
