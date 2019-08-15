<?php

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

$bind = [
    'table' => 'configuration',
    'whereCondition' => "id = :id",
    'bind' => [
        'id'=>9
    ],
];
go(function () use ($descriptor, $bind) {
    $mysql = new Mysql($descriptor);
    echo $mysql->delete($bind['table'], $bind['whereCondition'], $bind['bind']).PHP_EOL;
});

$mysql = new \Phalcon\Db\Adapter\Pdo\Mysql($descriptor);
echo $mysql->delete($bind['table'],$bind['whereCondition'],$bind['bind']);
