<?php

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

$sql = "SELECT Sleep(2)";

go(function () use ($descriptor, $sql) {


    $time = microtime(true);

    $mysql2 = new Mysql($descriptor);
    $mysql2->setDefer();
    $statement2 = $mysql2->query($sql);

    $mysql = new Mysql($descriptor);
    $mysql->setDefer();
    $statement = $mysql->query($sql);

    $result = $mysql->recv();
    $result2 = $mysql2->recv();

    if(!$result){
        echo "error:".$mysql->errno.$mysql->error.PHP_EOL;
    }
    if(!$result2){
        echo "error:".$mysql2->errno.$mysql2->error.PHP_EOL;
    }
    $row = $statement->fetch();
    $row2 = $statement2->fetch();
    echo microtime(true) - $time.PHP_EOL;
    print_r($row);
    print_r($row2);
});

