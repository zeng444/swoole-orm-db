<?php

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

$sql = "SELECT * FROM `configuration`  WHERE id=:id or id=:id2 LIMIT 0,1";
$bind = [
    'id' => 1,
    'id2' => 1,
];

go(function () use ($descriptor, $sql, $bind) {


    $mysql = new Mysql($descriptor);
    $result = $mysql->query($sql, $bind);
    //    $row = $result->fetch();
    while ($row = $result->fetch()) {
        print_r($row);
    }
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
