<?php

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

$bind = [
    'table' => 'configuration',
    'data' => ['companyId' => '1', 'key' => rand(1, 10000000)],
    'whereCondition' => "id = 9",
    'dataTypes' => null,
];
go(function () use ($descriptor, $bind) {
    $mysql = new Mysql($descriptor);
    echo $mysql->updateAsDict($bind['table'], $bind['data'], $bind['whereCondition'], $bind['dataTypes']).PHP_EOL;
});
//
$mysql = new \Phalcon\Db\Adapter\Pdo\Mysql($descriptor);
echo $mysql->updateAsDict($bind['table'], $bind['data'], $bind['whereCondition'], $bind['dataTypes']).PHP_EOL;
