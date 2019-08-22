<?php

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

$sql = "INSERT INTO `configuration` (`companyId`,`key`)values(:companyId,:key2)";
$bind = [
    'companyId' => 1,
    'key2' => 2,
];


go(function () use ($descriptor, $sql, $bind) {
    $mysql = new Mysql($descriptor);
    $mysql->begin();
    $result = $mysql->execute($sql,$bind);
    if(!$result){
        echo "error:".$mysql->errno.$mysql->error.PHP_EOL;
    }
    $mysql->commit();
    echo "success:".$mysql->affectedRows().PHP_EOL;

});