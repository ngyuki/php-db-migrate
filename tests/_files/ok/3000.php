<?php
return array(
    function (PDO $pdo) {
        $pdo->query("insert into tt values (3000)");
    },
    function (PDO $pdo) {
        $pdo->query("delete from tt where id = 3000");
    },
);
