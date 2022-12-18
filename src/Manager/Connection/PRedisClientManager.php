<?php

/*
 *  This file is a part of small-swoole-patterns
 *  Copyright 2022 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace Sebk\SmallSwoolePatterns\Manager\Connection;

use Predis\Client;
use Sebk\SmallSwoolePatterns\Manager\Contract\PoolManagerInterface;

class PRedisClientManager implements PoolManagerInterface
{

    /**
     * @param array|string $config
     */
    public function __construct(
        protected array|string $config,
    ) {}

    /**
     * Create redis client
     * @return Client
     */
    public function create(): Client
    {
        $client = new Client($this->config);
        $client->connect();

        return $client;
    }

    /**
     * @param Client $connection
     * @return void
     */
    public function close(mixed $connection)
    {
        $connection->disconnect();
    }

}