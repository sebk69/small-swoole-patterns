<?php

namespace Sebk\SmallSwoolePatterns\Test\Manager;

use PHPUnit\Framework\TestCase;
use Sebk\SmallSwoolePatterns\Manager\Connection\PRedisClientManager;

class PRedisClientManagerTest extends TestCase
{


    public function testClientCreation()
    {

        $manager = new PRedisClientManager('tcp://redis');

        $client = $manager->create();
        $client->connect();

        $this->assertTrue($client->isConnected());

    }

}