<?php

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

$sql = "SELECT * FROM `configuration`  WHERE id>:id or id<:id2 LIMIT 0,10";
$bind = [
    'id' => 1,
    'id2' => 112,
];


go(function () use ($descriptor, $sql, $bind) {

    $mysql = new Mysql($descriptor);
    $result = $mysql->fetchAll($sql,2, $bind);
    foreach ($result as $robot) {
        print_r($robot);
    }
});

$mysql = new \Phalcon\Db\Adapter\Pdo\Mysql($descriptor);
$result = $mysql->fetchAll($sql,2, $bind);
foreach ($result as $robot) {
    print_r($robot);
}
