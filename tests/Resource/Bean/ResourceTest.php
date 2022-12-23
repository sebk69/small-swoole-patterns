<?php

namespace Sebk\SmallSwoolePatterns\Test\Resource\Bean;

use PHPUnit\Framework\TestCase;
use Sebk\SmallSwoolePatterns\Manager\Connection\PRedisClientManager;
use Sebk\SmallSwoolePatterns\Manager\StoredListManager\PRedisStoredListManager;
use Sebk\SmallSwoolePatterns\Pool\Pool;
use Sebk\SmallSwoolePatterns\Resource\Bean\Resource;
use Sebk\SmallSwoolePatterns\Resource\Bean\Ticket;
use Sebk\SmallSwoolePatterns\Resource\Enum\GetResourceBehaviour;
use Sebk\SmallSwoolePatterns\Resource\Exception\ResourceNotFreeException;

class ResourceTest extends TestCase
{

    public function testBasic()
    {
        
        $resource = new Resource('test', new PRedisStoredListManager(new Pool(new PRedisClientManager('tcp://redis'), 1)));
        $ticket = $resource->acquireResource(GetResourceBehaviour::exceptionIfNotFree);
        $this->assertInstanceOf(Ticket::class, $ticket);
        $resource->releaseResource($ticket);
        
    }
    
    public function testAcquireRelease()
    {
        $resource = new Resource('test', new PRedisStoredListManager(new Pool(new PRedisClientManager('tcp://redis'), 1)));
        $ticket = $resource->acquireResource(GetResourceBehaviour::exceptionIfNotFree);
        $this->expectException(ResourceNotFreeException::class);
        $resource->acquireResource(GetResourceBehaviour::exceptionIfNotFree);
        $ticket2 = $resource->acquireResource(GetResourceBehaviour::getTicket);
        $this->assertTrue($ticket2->isWaiting());
        $resource->releaseResource($ticket);
        $this->assertFalse($ticket2);
    }

}