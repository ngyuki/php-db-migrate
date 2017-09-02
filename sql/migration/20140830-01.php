<?php
return array(
    function (PDO $pdo) {
        $stmt = $pdo->prepare("insert into tt values (?)");
        for ($i=3000; $i<3100; $i++) {
            $stmt->execute(array($i));
        }
    },
    function (PDO $pdo) {
        $pdo->query("delete from tt where id between 3000 and 3100");
    },
);
