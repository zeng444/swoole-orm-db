<?php

use Janfish\Swoole\Pool\Db\Mysql;

include_once '../vendor/autoload.php';
$descriptor = [
    "host" => "mysql",
    "username" => "root",
    "password" => "root",
    "dbname" => "car_insurance_genius_v2_prod",
    "port" => 3306,
    "charset" => 'utf8mb4',
    'options' => [
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
    ],
];
//$sql = "SELECT * FROM `configuration`  WHERE id=? or id=? LIMIT ?,?";
//$bind = [1, 2, 0, 2];

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