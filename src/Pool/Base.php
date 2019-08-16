<?php

namespace Janfish\Swoole\Pool;

abstract class Base
{

    /**
     * @var \Swoole\Coroutine\Channel
     */
    protected $pool;

    /**
     * Author:Robert
     *
     * @var mixed
     */
    protected $max;

    /**
     * Author:Robert
     *
     * @var mixed
     */
    protected $min;

    /**
     * Author:Robert
     *
     * @var int 
     */
    protected $createdConnection = 0;


    /**
     * Base constructor.
     * @param $options
     */
    function __construct(array $options)
    {
        if (isset($options['max'])) {
            $this->max = $options['max'];
        }
        if (isset($options['min'])) {
            $this->min = $options['min'];
        }
        $this->pool = new \Swoole\Coroutine\Channel($this->min);

    }

    protected function init()
    {
        $quantity = ceil(($this->max - $this->min) / 2);
        for ($i = 0; $i < $quantity; $i++) {
            $this->addConnection($this->createConnection());
        }
    }


    /**
     * Author:Robert
     *
     * @return bool
     */
    abstract function createConnection(): bool;

    /**
     * Author:Robert
     *
     * @param $connection
     * @return mixed
     */
    abstract function closeConnection($connection): bool;


    /**
     * Author:Robert
     *
     * @param $connection
     */
    public function removeConnection($connection)
    {
        $this->closeConnection($connection);
        $this->createdConnection--;
    }


    /**
     * Author:Robert
     *
     * @param $connection
     */
    private function addConnection($connection)
    {
        $this->pool->push($connection);
        $this->createdConnection++;
    }


    /**
     * Author:Robert
     *
     * @param $timeout
     * @return mixed
     */
    public function getConnection(int $timeout)
    {
        if ($this->isEmpty() && $this->createdConnection < $this->max) {
            $this->addConnection($this->createConnection());
        }
        $connection = $this->pool->pop($timeout);
        return $connection;
    }

    /**
     * Author:Robert
     *
     * @param $connection
     * @param $timeout
     * @return bool
     */
    public function return($connection, int $timeout): bool
    {
        if ($this->isFull()) {
            $this->removeConnection($connection);
            return false;
        }
        $result = $this->pool->push($connection, $timeout);
        if (!$result) {
            $this->removeConnection($connection);
        }
        return true;
    }


    /**
     * Author:Robert
     *
     * @return int
     */
    public function length(): int
    {
        return $this->pool->length();
    }


    /**
     * Author:Robert
     *
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->pool->isFull();
    }

    /**
     * Author:Robert
     *
     * @return int
     */
    public function createdConnection(): int
    {
        return $this->createdConnection;
    }

    /**
     * Author:Robert
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->pool->isEmpty();
    }

}
