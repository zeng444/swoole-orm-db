<?php

namespace Janfish\Swoole;

/**
 * Author:Robert
 *
 * Class Pool
 * @package Janfish\Swoole
 */
abstract class Pool
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
     * @var
     */
    protected $minSpare;

    /**
     * @var int|mixed
     */
    protected $minSpareMinute = 20;

    /**
     * @var
     */
    protected $maxSpare;

    /**
     * Author:Robert
     *
     * @var int
     */
    protected $createdConnection = 0;

    /**
     * @var int|mixed
     */
    protected $gcLoopMinute = 5;


    /**
     * Base constructor.
     * @param $options
     */
    function __construct(array $options)
    {
        if (isset($options['max'])) {
            $this->max = $options['max'];
        }
        if (isset($options['min_spare_minute'])) {
            $this->minSpareMinute = $options['min_spare_minute'];
        }
        if (isset($options['min'])) {
            $this->min = $options['min'];
        }
        if (isset($options['min_spare'])) {
            $this->minSpare = $options['min_spare'];
        }
        if (isset($options['max_spare'])) {
            $this->maxSpare = $options['max_spare'];
        }
        if (isset($options['gc_loop_minute'])) {
            $this->gcLoopMinute = $options['gc_loop_minute'];
        }
        $this->pool = new \Swoole\Coroutine\Channel($this->min);

    }


    /**
     * Author:Robert
     *
     * @return bool
     */
    abstract function createConnection();

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
     * @throws \Exception
     */
    protected function init()
    {
        $quantity = ceil(($this->max - $this->min) / 2);
        for ($i = 0; $i < $quantity; $i++) {
            $connection = $this->createConnection();
            if (!$connection) {
                throw new \Exception('sorry failure to create pool connection,PLZ check your connect method ');
            }
            $this->addConnection($connection);
        }
    }


    /**
     * Author:Robert
     *
     * @param $connection
     * @return bool
     */
    protected function removeConnection($connection): bool
    {
        if (!$this->closeConnection($connection)) {
            return false;
        }
        $this->createdConnection--;
        return true;
    }


    /**
     * Author:Robert
     *
     * @param $connection
     * @return bool
     */
    protected function addConnection($connection): bool
    {
        $data = [
            'lastAt' => time(),
            'connection' => $connection,
        ];
        if (!$this->pool->push($data)) {
            return false;
        }
        $this->createdConnection++;
        return true;
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
        $handler = $this->pool->pop($timeout);
        return $handler['connection'];
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
        $handler = [
            'lastAt' => time(),
            'connection' => $connection,
        ];
        $result = $this->pool->push($handler, $timeout);
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

    /**
     * Author:Robert
     *
     */
    public function gc()
    {
        \Swoole\Timer::tick(120000, function () {
            $list = [];
            $lenth = $this->length();
            if ($lenth > $this->maxSpare) {
                while (!$this->isEmpty()) {
                    $handler = $this->pool->pop(0.001);
                    $lastAt = $handler['lastAt'] ?? 0;
                    if (time() - $lastAt > 60 * $this->minSpareMinute && $this->createdConnection >= $this->min) {
                        $this->closeConnection($handler['connection']);
                    } else {
                        array_push($list, $handler);
                    }
                }
                foreach ($list as $item) {
                    $this->pool->push($item);
                }
            } elseif ($lenth < $this->minSpare && $this->createdConnection < $this->max) {
                $quantity = floor(($this->max - $lenth) / 2);
                for ($i = 0; $i < $quantity; $i++) {
                    $this->addConnection($this->createConnection());
                }
            }
        });
    }

}
