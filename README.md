# small-swoole-patterns
## About
This project provides implementations of async design patterns to open swoole projects.

## Install

Install openswoole :
```
pecl install openswoole
```

Require the package with composer:
```
composer require sebk/small-swoole-patterns
```

## Patterns

### Array

#### Map

This is an evolution of swoole function \Coroutine\map

It is a class to map array on a callback. More verbose than \Coroutine\map but allow you waiting later in code.

```php
<?php
use Sebk\SmallSwoolePatterns\Array\Map;

// First map
$map1 = (new Map([1, 2, 3], function($value) {
    return $value - 1;
}))->run();

// Second map
$map2 = (new Map([4, 6, 8], function($value) {
    return $value / 2
}))->run();

// Wait two maps finished
$map1->wait();
$map2->wait();

// Merg results and dump result;
$result = array_merge($map1, $map2);
var_dump($result);
```

### Observable

This is an implemntation of observable pattern on a callback.

```php
use Sebk\SmallSwoolePatterns\Observable\Observable;

// Create callback
$get = function ($host): string {
    return (new Coroutine\Http\Client($host, 80, true))
        ->get('/');
};

// Create observer
$getObserver = (new Observable($get))
    ->subscribe(function(string $html) {
        // left method handle result
        echo $html;
    }, function(\Exception $e) {
        // Right method handle exceptions
        echo 'Can\'t get url : ' . $e->getMessage();
    })
;

$getObserver
    ->run('www.google.com')
    ->run('www.yahoo.com')
    ->run('www.qwant.com')
;

$getObserver->wait();
```

In this example, we print homepage of google, yahoo and qwant on async calls.