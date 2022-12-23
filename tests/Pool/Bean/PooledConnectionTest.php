<?php

namespace Sebk\SmallSwoolePatterns\Test\Pool\Bean;

use PHPUnit\Framework\TestCase;
use Sebk\SmallSwoolePatterns\Pool\Bean\PooledConnection;
use Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionBusyException;

class PooledConnectionTest extends TestCase
{

    const CONNECTION_STUB = "Strub";

    private PooledConnection $pooledConnection;

    public function setUp(): void
    {
        $this->pooledConnection = new PooledConnection(static::CONNECTION_STUB);

        parent::setUp();
    }

    public function testGetConnection()
    {

        $this->assertEquals(static::CONNECTION_STUB, $this->pooledConnection->getConnection());

    }

    public function testLocks()
    {

        $this->pooledConnection->lock();
        $this->expectException(PooledConnectionBusyException::class);
        $this->pooledConnection->lock();
        $this->pooledConnection->unlock();
        $this->pooledConnection->lock();
        
    }

}