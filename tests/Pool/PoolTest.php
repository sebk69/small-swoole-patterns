<?php

namespace Sebk\SmallSwoolePatterns\Test\Pool;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Sebk\SmallSwoolePatterns\Array\Map;
use Sebk\SmallSwoolePatterns\Manager\Connection\PRedisClientManager;
use Sebk\SmallSwoolePatterns\Pool\Bean\RateController;
use Sebk\SmallSwoolePatterns\Pool\Pool;

class PoolTest extends TestCase
{

    public function testGetManager()
    {

        $pool = new Pool(
            new PRedisClientManager('tcp://redis'),
            10,
            10,
        );
        $this->assertInstanceOf(PRedisClientManager::class, $pool->getManager());

    }

    public function testGetClient()
    {
        $pool = new Pool(
            new PRedisClientManager('tcp://redis'),
            10,
            10,
        );

        // Test instance of client
        /** @var Client $client */
        $client = $pool->get();
        $this->assertInstanceOf(Client::class, $client);
        $pool->put($client);
    }

    public function testMaxConnectors()
    {

        $pool = new Pool(
            new PRedisClientManager('tcp://redis'),
            2,
            1000,
        );

        $start = microtime(true);
        (new Map(range(1, 3), function () use ($pool) {
            $pool->put($pool->get());
        }))->run()->wait();
        $this->assertLessThanOrEqual(0.01, microtime(true) - $start);

    }

    public function testRate()
    {
        ($pool = new Pool(
            new PRedisClientManager('tcp://redis'),
            3,
            1000,
        ))->activateRateController(1000, 10);

        $this->assertInstanceOf(RateController::class, $pool->getRateController());

        $start = microtime(true);
        (new Map(range(1, 3), function () use ($pool) {
            $pool->put($pool->get());
        }))->run()->wait();
        $this->assertGreaterThanOrEqual(0.03, microtime(true) - $start);
        $this->assertLessThanOrEqual(0.04, microtime(true) - $start);
    }

}