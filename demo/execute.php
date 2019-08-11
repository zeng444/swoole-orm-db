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

$sql = "INSERT INTO `configuration` (`companyId`,`key`)values(:companyId,:key2)";
$bind = [
    'companyId' => 1,
    'key2' => 2,
];


go(function () use ($descriptor, $sql, $bind) {
    $mysql = new Mysql($descriptor);
    $result = $mysql->execute($sql,$bind);
    if(!$result){
        echo "error:".$mysql->errno.$mysql->error.PHP_EOL;
    }
    echo "success:".$mysql->affectedRows().PHP_EOL;

});

$mysql = new \Phalcon\Db\Adapter\Pdo\Mysql($descriptor);
$result = $mysql->execute($sql,$bind);
if(!$result){
    echo "error:".PHP_EOL;
}
echo "success:".$mysql->affectedRows().PHP_EOL;
