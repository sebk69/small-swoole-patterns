<?php

namespace Sebk\SmallSwoolePatterns\Test\Manager;

use PHPUnit\Framework\TestCase;
use Sebk\SmallSwoolePatterns\Manager\Connection\PRedisClientManager;
use Sebk\SmallSwoolePatterns\Manager\StoredListManager\PRedisStoredListManager;
use Sebk\SmallSwoolePatterns\Pool\Pool;

class PRedisStoredListManagerTest extends TestCase
{

    private PRedisStoredListManager $list;
    
    public function setUp(): void
    {
        $this->list = new PRedisStoredListManager((new Pool(new PRedisClientManager('tcp://redis'), 10)));
        
        // Setup list
        $this->list->reset('test');
        $this->list->lpush('test', 1);
        $this->list->lpush('test', 2);
        $this->list->rpush('test', 3);
    }

    public function testPop()
    {
        
        // Get all without popping
        $array = $this->list->all('test');
        $this->assertIsArray($array);
        $this->assertEquals(3, count($array));
        $this->assertEquals(2, $array[0]);
        $this->assertEquals(1, $array[1]);
        $this->assertEquals(3, $array[2]);

        $this->assertEquals(2, $this->list->lpop('test'));
        $this->assertEquals(3, $this->list->rpop('test'));
        $this->assertEquals(1, $this->list->lpop('test'));

    }

    public function testRemove()
    {

        $this->list->lrem('test', 2, 1);
        $this->assertEquals(1, $this->list->lpop('test'));
        $this->assertEquals(3, $this->list->lpop('test'));

    }

    public function testReset()
    {

        $this->list->reset('test');
        $array = $this->list->all('test');

        $this->assertIsArray($array);
        $this->assertEquals(0, count($array));

    }

}