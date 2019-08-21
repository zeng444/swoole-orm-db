<?php

namespace Janfish\Swoole\Pool\Db;


use Janfish\Swoole\Pool;
use Janfish\Swoole\Pool\PoolInterface;

class Mysql extends Pool implements PoolInterface
{


    /**
     * Author:Robert
     *
     * @return bool
     */
    public function createConnection(): bool
    {

    }

    /**
     * Author:Robert
     *
     * @param $connection
     * @return bool
     */
    public function closeConnection($connection): bool
    {

    }

}
