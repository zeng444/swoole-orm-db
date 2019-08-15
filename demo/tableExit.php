<?php

use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql;

include_once '../vendor/autoload.php';
$descriptor = require_once 'configs/db.php';
go(function () use ($descriptor) {
    $mysql = new Mysql($descriptor);
    var_dump($mysql->tableExists('configuration'));
});

//$mysql = new \Phalcon\Db\Adapter\Pdo\Mysql($descriptor);
//var_dump($mysql->tableExists('configuration'));
