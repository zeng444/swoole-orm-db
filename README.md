# Phalcon DB Layer 协程版

## 特色

- 基于Swoole Mysql驱动重写的phalcon db layer，方法和phalcon手册一致
- 基于Swoole实现连接池，可以基于连接池基类实现各种类型连接池
- 基于连接池实现的协程版phalcon db layer
- 基于swoole常驻内存的特点，增加了数据库断线自动重连

## 基本操作同

https://docs.phalconphp.com/3.4/en/db-models

## 协程操作

- 申明defer和recv实现协程通讯

```php

$db =  $di->db;
$db2 =  $di->db;


$db->setDefer();
$db2->setDefer();

$result = $mysql->query($sql);
$result2 = $mysql2->query($sql);

$db->recv();
$db2->recv();

$row = $result->fetch();
$row2 = $result2->fetch();

print_r($row);
print_r($row2);

```

- 写入通讯并发

```php
for($i=0;$i<100,$++){
    $sql= '.....';
    $db = $di->db;
    $db->setDefer();
    $result = $db->execute($sql);
    $db->recv();
    if (!$result) {
        echo "error:".$mysql->errno.$mysql->error.PHP_EOL;
    }
}
```

## 数据库连接池

- 已经集成在Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Myql
- 创建带有数据库连接池的数据库实例和标准单一数据库实例

```php
use Janfish\Swoole\Coroutine\Db\Adapter\Pdo\Myql;
use Janfish\Swoole\Pool\Db\Mysql as MysqlPool

// 连接配置
$descriptor = [
    "host" => "mysql",
    "username" => "root",
    "password" => "root",
    "dbname" => "car_insurance_genius_v2_prod",
    "port" => 3306,
    "charset" => 'utf8mb4',
    'options' => [
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
    ],
];

// 普通数据库实例
$mysql = new Myql( $descriptor);

// 数据库连接池的数据库实例
$mysql = new Myql(new MysqlPool($descriptor,[
   'min'=>100', //最小连接进程
   'max'=>100, //最大连接进程
   'min_spare_minute'=>2, //gc回收周期单位分钟
   'min_spare'=>100, //最小空闲进程
   'max_spare'=>100 //最大空闲进程
]) );

```

## 连接池

- 实现一个Redis连接池


```php
use Janfish\Swoole\Pool;
use Janfish\Swoole\Pool\PoolInterface;

/**
 *
 * Class RedisPool
 */
class RedisPool extends Pool implements PoolInterface
{

    /**
     * @var array|null
     */
    private $_descriptor = [];


    /**
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
        $connection = new Redis($this->_descriptor);
        if (!$connection->connect()) {
            throw new Exception('failure to create connection for redis pool');
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
```

## 连接池方法

```
$pool = new RedisPool(['host'=>'127.0.0.1'],['min'=>10,'max'=>1000]);
$redis = $pool->getConnection();
$redis->set("key","val");
$redis->returnConnection();
```
