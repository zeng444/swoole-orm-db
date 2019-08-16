<?php

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

$sql = "INSERT INTO `configuration` (`companyId`,`key`)values(:companyId,:key2)";
$bind = [
    'companyId' => 1,
    'key2' => microtime(),
];


go(function () use ($descriptor, $sql, $bind) {
    $mysql = new Mysql($descriptor);
    $mysql->setDefer();
    $result = $mysql->execute($sql, $bind);
    $mysql->recv();
    if (!$result) {
        echo "error:".$mysql->errno.$mysql->error.PHP_EOL;
    }
    echo "success:".$mysql->affectedRows().PHP_EOL;
    echo "success:".$mysql->lastInsertId().PHP_EOL;

});

//$mysql = new \Phalcon\Db\Adapter\Pdo\Mysql($descriptor);
//$result = $mysql->execute($sql,$bind);
//if(!$result){
//    echo "error:".PHP_EOL;
//}
//echo "success:".$mysql->affectedRows().PHP_EOL;
