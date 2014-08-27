<?php
$env = new \TestHelper\TestEnv();
$pdo = $env->pdo();

return array(
    'pdo' => $pdo,
    'extract' => array('pdo' => $pdo),
    'directory' => 'ok',
);
