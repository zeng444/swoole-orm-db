<?php

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

go(function () {
    $config = [
        'host' => 'mysql',
        'user' => 'root',
        'password' => 'root',
        'database' => 'car_insurance_genius_v2_prod',
        'fetch_mode' => true,
    ];
    $sql = "SELECT Sleep(2)";

    $time = microtime(true);
    $mysql = new Swoole\Coroutine\MySQL();
    $mysql->connect($config);
    $stmt = $mysql->prepare($sql);

    $mysql2 = new Swoole\Coroutine\MySQL();
    $mysql2->connect($config);
    $stmt2 = $mysql2->prepare($sql);
    $mysql->setDefer();
    $stmt->execute();

    $mysql2->setDefer();
    $stmt2->execute();
    $stmt->recv();
    $stmt2->recv();

    $result = $stmt->fetch();
    $result2 = $stmt2->fetch();
    echo microtime(true) - $time.PHP_EOL;
    print_r($result);
    print_r($result2);
});
