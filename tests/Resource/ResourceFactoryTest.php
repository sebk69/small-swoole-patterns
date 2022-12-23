<?php

namespace Sebk\SmallSwoolePatterns\Test\Resource;

use PHPUnit\Framework\TestCase;
use Sebk\SmallSwoolePatterns\Manager\Connection\PRedisClientManager;
use Sebk\SmallSwoolePatterns\Manager\StoredListManager\PRedisStoredListManager;
use Sebk\SmallSwoolePatterns\Pool\Pool;
use Sebk\SmallSwoolePatterns\Resource\Bean\Resource;
use Sebk\SmallSwoolePatterns\Resource\ResourceFactory;

class ResourceFactoryTest extends TestCase
{

    public function testBasic()
    {

        $factory = new ResourceFactory([
            'test1', 'test2'
        ], new PRedisStoredListManager(new Pool(new PRedisClientManager('tcp://redis'), 1)));

        $this->assertInstanceOf(Resource::class, $factory->get('test1'));
        $this->assertInstanceOf(Resource::class, $factory->get('test2'));
        $this->assertInstanceOf(Resource::class, $factory->get('test3'));

    }

}