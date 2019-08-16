<?php

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

$sql = "SELECT * FROM `configuration`  WHERE id>:id or id<:id2 LIMIT 0,1";
$bind = [
    'id' => 0,
    'id2' => 1000,
];
$mode = 2;

go(function () use ($descriptor, $sql, $bind) {

    $mysql = new Mysql($descriptor);
    $result = $mysql->fetchAll($sql,3, $bind);
    var_dump($result);
    foreach ($result as $robot) {
//        var_dump($robot);
    }
});

$mysql = new \Phalcon\Db\Adapter\Pdo\Mysql($descriptor);
$result = $mysql->fetchAll($sql,3, $bind);

foreach ($result as $robot) {
    var_dump($robot);
}
