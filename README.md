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

This is an implementation of observable pattern on a callback.

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


### Pool

This is an implementation of pool pattern.

Pools are useful to manage async processes in order to manage connections to server resources.

#### Create the Pool
```php
$pool = new \Sebk\SmallSwoolePatterns\Pool\Pool(
    new \Sebk\SmallSwoolePatterns\Manager\Connection\PRedisClientManager('tcp://my-redis.net'),
    10,
    100
);
```

Here we have created
- A PRedis client pool (first parameter)
- With a maximum of 10 clients at the same time (second parameter).
- If no more clients available, the pool try to lock a new client every 100µs (third parameter)

### Using client process

To get a client, use get method :
```php
$client = $pool->get();
```

You have now locked the client and can use it :
```php
$client->get('my-app:key')
```

Now we have finished the use, we must release the client :
```php
$pool->put($client);
```

Putting together in async process will be :
```php
$pool = new \Sebk\SmallSwoolePatterns\Pool\Pool(
    new \Sebk\SmallSwoolePatterns\Manager\Connection\PRedisClientManager('tcp://my-redis.net'),
    10,
    100
);

(new \Sebk\SmallSwoolePatterns\Array\Map(range(1, 100), ($i) use($pool) => {
    $client = $pool->get();
    $client->put('my-app:sum:' . $i, $i +$i);
    $pool->put($client);
}));
```

In this use case, the number of concurrent connections can't be more than 10 connections at the same time even the process is async.

Using one client destroy the async advantages while using client will wait for previous end.

Using a new client at each time can overload your memory and server.

### Rate control

You can control the server limitations using rate control.

#### Activating rate control

```php
($pool = new \Sebk\SmallSwoolePatterns\Pool\Pool(
    new \Sebk\SmallSwoolePatterns\Manager\Connection\PRedisClientManager('tcp://my-redis.net'),
    10,
    100
))->activateRateController(100, 10);
```

In this code, the pool is waiting you are getting clients no more than 100µs. If less, it will wait 10µs before retry.

#### Using rate control for server limitation

For this example, we will consider that you want to connect to a provider http api. You have a limitation of 3000 requests by minutes.

You can activate a unit control to observe the provider limitations even your code is faster :
```php
($pool = new \Sebk\SmallSwoolePatterns\Pool\Pool(
    new \Sebk\SmallSwoolePatterns\Manager\Connection\HttpClientManager('api.my-provider.net'),
    10,
    100
))->activateRateController(100, 10)
    ->addUnitToControl('minutes', 60, \Sebk\SmallSwoolePatterns\Pool\Enum\RateBehaviour::waiting, 3000);

(new \Sebk\SmallSwoolePatterns\Array\Map(range(1, 100), ($productId) use($pool) => {
    $client = $pool->get();
    $uri = 'getProduct/{productId}'
    $product = json_decode($client->get(str_replace('{productId}', $productId, $uri)));
    $pool->put($client);
    
    return $product;
}))->run()->wait();
```

### Resource

You can manage resource access with resource pattern :
```php
$factory = new \Sebk\SmallSwoolePatterns\Resource\ResourceFactory();
$resource = $factory->get('testResource1');
$ticket = $resource->acquireResource(\Sebk\SmallSwoolePatterns\Resource\Enum\GetResourceBehaviour::exceptionIfNotFree);
$resource->releaseResource($ticket);
```

In async processes you can wait the others processes to unlock resource :
```php
$factory = new \Sebk\SmallSwoolePatterns\Resource\ResourceFactory();
$resource = $factory->get('testResource1');
$ticket = $resource->acquireResource(\Sebk\SmallSwoolePatterns\Resource\Enum\waitingForFree);
$resource->releaseResource($ticket);
```

Or manage yourself the waiting process :
```php
$factory = new \Sebk\SmallSwoolePatterns\Resource\ResourceFactory();
$resource = $factory->get('testResource1');
$ticket = $resource->acquireResource(\Sebk\SmallSwoolePatterns\Resource\Enum\GetResourceBehaviour::getTicket);
while ($ticket->isWaiting()) {
    doStuff();
    usleep(100);
}
doResourceStuff();
$resource->releaseResource($ticket);
```