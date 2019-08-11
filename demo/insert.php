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

$sql = "SELECT * FROM `configuration`  WHERE id>:id or id<:id2 LIMIT 0,10";
$bind = [
    ['1','2'],
    ['companyId','key'],
];


go(function () use ($descriptor, $sql, $bind) {
    $mysql = new Mysql($descriptor);
    echo $mysql->insert('configuration',$bind[0],$bind[1]);
});

$mysql = new \Phalcon\Db\Adapter\Pdo\Mysql($descriptor);
echo $mysql->insert('configuration',$bind[0],$bind[1]);
