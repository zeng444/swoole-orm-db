<?php

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

//$sql = "SELECT * FROM `configuration`  WHERE id=:id or id=:id2 LIMIT 0,1";
$sql = "SELECT Sleep(2)";
$bind = [
    'id' => 1,
    'id2' => 1,
];

go(function () use ($descriptor, $sql, $bind) {

    $time = microtime(true);
    $mysql = new Mysql($descriptor);
    $mysql2 = new Mysql($descriptor);
    $mysql->setDefer();
    $mysql2->setDefer();
    $result = $mysql->query($sql);
    $result2 = $mysql2->query($sql);
    $mysql->recv();
    $mysql2->recv();
    $row = $result->fetch();
    $row2 = $result2->fetch();
    echo microtime(true)-$time.PHP_EOL;
    print_r($row);
    print_r($row2);

});

//$mysql = new \Phalcon\Db\Adapter\Pdo\Mysql($descriptor);
//$result = $mysql->query($sql, $bind);
//Phalcon\Db::FETCH_NUM;
//Phalcon\Db::FETCH_ASSOC;
//Phalcon\Db::FETCH_BOTH;
//Phalcon\Db::FETCH_OBJ;

//$result->setFetchMode(Phalcon\Db::FETCH_NUM);
//while ($row = $result->fetch()) {
//    print_r($row);
//}
