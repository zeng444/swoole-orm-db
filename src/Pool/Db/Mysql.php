<?php

namespace Janfish\Swoole\Pool\Db;

use Janfish\Swoole\Pool;
use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Mysql as PdoMysql;
use Janfish\Swoole\Pool\PoolInterface;
use Swoole\Exception;

/**
 * Author:Robert
 *
 * Class Mysql
 * @package Janfish\Swoole\Pool\Db
 */
class Mysql extends Pool implements PoolInterface
{

    /**
     * @var array|null
     */
    private $_descriptor = [];


    /**
     * Mysql constructor.
     * @param array|null $descriptor
     * @param array $options
     */
    public function __construct(array $descriptor, array $options)
    {
        if ($descriptor) {
            $this->_descriptor = $descriptor;
        }
        parent::__construct($options);
    }

    /**
     * Author:Robert
     *
     * @return bool|PdoMysql
     * @throws Exception
     */
    public function createConnection()
    {
        $connection = new PdoMysql($this->_descriptor);
        if (!$connection->connect()) {
            throw new Exception('failure to create connection for mysql pool');
        }
        return $connection;
    }

    /**
     * Author:Robert
     *
     * @param PdoMysql $connection
     * @return bool
     */
    public function closeConnection($connection): bool
    {
        return $connection->close();
    }

}
