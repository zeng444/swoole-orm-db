<?php

namespace Janfish\Swoole\Pool;

interface PoolInterface
{


    /**
     * Author:Robert
     *
     * @return bool
     */
    public function createConnection();

    /**
     * Author:Robert
     *
     * @param $connection
     * @return mixed
     */
    public function closeConnection($connection): bool;
}
