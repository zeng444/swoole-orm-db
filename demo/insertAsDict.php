<?php
use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';

$sql = "SELECT * FROM `configuration`  WHERE id>:id or id<:id2 LIMIT 0,10";
$bind = [
    'companyId' => 1,
    'key' => 2,
];


go(function () use ($descriptor, $sql, $bind) {
    $mysql = new Mysql($descriptor);
    echo $mysql->insertAsDict('configuration', $bind);
});

$mysql = new \Phalcon\Db\Adapter\Pdo\Mysql($descriptor);
echo $mysql->insertAsDict('configuration', $bind);
