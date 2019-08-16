<?php

namespace Janfish\Swoole\Pool\Db;


use Janfish\Swoole\Pool\Base;
use Janfish\Swoole\Pool\PoolInterface;

class Mysql extends Base implements PoolInterface
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
