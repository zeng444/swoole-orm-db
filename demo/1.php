
## 单线程让出
```
go(function() {
    go(function () {
        co::sleep(3.0);
        go(function () {
            co::sleep(2.0);
            echo "3\n";
        });
        echo "2\n";
    });

    co::sleep(1.0);
    echo "1\n";
});
```
```
输出 123
```
## 单线程的顺序执行

```
go(function() {
    go(function () {
        echo "2\n";
        go(function () {
            echo "3\n";
        });
    });
    echo "1\n";
});
```
```
输出 231
```




## 标准单线程的顺序执行
```
echo '1'.PHP_EOL;
\Swoole\Coroutine::create(function () {
    echo '2'.PHP_EOL;
});
echo '3'.PHP_EOL;
```
```
输出 123
```

## sleep引发同步阻塞
```
echo "=========================".PHP_EOL;
echo '1'.PHP_EOL;
\Swoole\Coroutine::create(function () {
    sleep(1); //同步阻塞
    echo '2'.PHP_EOL;
});
echo '3'.PHP_EOL;
```
```
输出 123
```


## \Swoole\Coroutine::sleep非阻塞让出
```
echo "=========================".PHP_EOL;
echo '1'.PHP_EOL;
\Swoole\Coroutine::create(function () {
    \Swoole\Coroutine::sleep(1); //异步让出
    echo '2'.PHP_EOL;
});
echo '3'.PHP_EOL;
```
```
输出 132
```

## 协程顺序顺序执行和变量共享
```
$a = 0;
echo $a.PHP_EOL;
\Swoole\Coroutine::create(function () use (&$a) {
    echo $a.PHP_EOL;
    //清理变量
    \Swoole\Coroutine::defer(function () use (&$a) {
        $a = 3;
        echo $a.PHP_EOL;
    });
});
echo $a.PHP_EOL;
```
```
输出 0033
```