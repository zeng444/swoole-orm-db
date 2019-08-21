<?php

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

$bind = [
    ['1', '2'],
    ['companyId', 'key'],
];


go(function () use ($descriptor, $bind) {
    $mysql = new Mysql($descriptor);
    $mysql->setDefer();
    $result = $mysql->insert('configuration', $bind[0], $bind[1]);
    echo $result.PHP_EOL;
    $mysql->recv();
    echo $result.PHP_EOL;
    echo "success:".$mysql->affectedRows().PHP_EOL;
    echo "success:".$mysql->lastInsertId().PHP_EOL;
});
//
//$mysql = new \Phalcon\Db\Adapter\Pdo\Mysql($descriptor);
//echo $mysql->insert('configuration',$bind[0],$bind[1]);
