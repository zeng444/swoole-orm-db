<?php

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

$bind = [
    'table' => 'configuration',
    'fields' => ['companyId', 'configuration.key'],
    'values' => ['1', rand(1, 10000000)],
    'whereCondition' => [
        "conditions" => "id = ?",
        'bind' => [9],
    ],
    'dataTypes' => null,
];
go(function () use ($descriptor, $bind) {
    $mysql = new Mysql($descriptor);
    echo $mysql->update($bind['table'], $bind['fields'], $bind['values'], $bind['whereCondition'], $bind['dataTypes']).PHP_EOL;
});

$mysql = new \Phalcon\Db\Adapter\Pdo\Mysql($descriptor);
echo $mysql->update($bind['table'],$bind['fields'],$bind['values'],$bind['whereCondition'],$bind['dataTypes']);
