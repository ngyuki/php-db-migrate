<?php
/* @var $pdo PDO */
$stmt = $pdo->prepare("insert into tt values (?)");

for ($i=3000; $i<3100; $i++)
{
    $stmt->execute(array($i));
}
