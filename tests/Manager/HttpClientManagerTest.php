<?php

namespace Sebk\SmallSwoolePatterns\Test\Manager;

use PHPUnit\Framework\TestCase;
use Sebk\SmallSwoolePatterns\Manager\Connection\HttpClientManager;

class HttpClientManagerTest extends TestCase
{

    public function testClientCreationByHost(): void
    {

        $manager = new HttpClientManager('www.google.com', 8080, true);

        $connection = $manager->create();

        $this->assertEquals('www.google.com', $connection->host);
        $this->assertEquals(8080, $connection->port);
        $this->assertTrue($connection->ssl);

    }

    public function testClientCreationByIp(): void
    {

        $manager = new HttpClientManager('192.168.10.12', 8080, true);

        $connection = $manager->create();

        $this->assertEquals('192.168.10.12', $connection->host);
        $this->assertEquals(8080, $connection->port);
        $this->assertTrue($connection->ssl);

    }

}