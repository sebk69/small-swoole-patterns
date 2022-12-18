<?php

namespace Sebk\SmallSwoolePatterns\Test\Manager;

use PHPUnit\Framework\TestCase;
use Sebk\SmallSwoolePatterns\Manager\Connection\PRedisClientManager;
use Sebk\SmallSwoolePatterns\Manager\StoredListManager\PRedisStoredListManager;
use Sebk\SmallSwoolePatterns\Pool\Pool;

class PRedisStoredListManagerTest extends TestCase
{

    public function testList()
    {
        $list = new PRedisStoredListManager((new Pool(new PRedisClientManager('tcp://redis'), 10)));

        // Setup list
        $list->reset('test');
        $list->lpush('test', 1);
        $list->lpush('test', 2);
        $list->rpush('test', 3);

        // Get all without popping
        $array = $list->all('test');
        $this->assertIsArray($array);
        $this->assertEquals(3, count($array));
        $this->assertEquals(2, $array[0]);
        $this->assertEquals(1, $array[1]);
        $this->assertEquals(3, $array[2]);

        $this->assertEquals(2, $list->lpop('test'));
        $this->assertEquals(3, $list->rpop('test'));
        $this->assertEquals(1, $list->lpop('test'));
    }

}