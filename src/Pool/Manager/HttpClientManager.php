<?php

/*
 *  This file is a part of small-swoole-patterns
 *  Copyright 2022 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace Sebk\SmallSwoolePatterns\Pool\Manager;

use Sebk\SmallSwoolePatterns\Pool\Contract\PoolManagerInterface;
use Sebk\SmallSwoolePatterns\Pool\Exception\InvalidHostnameException;
use Sebk\SmallSwoolePatterns\Pool\Exception\InvalidParameterException;
use Sebk\SmallSwoolePatterns\Pool\Exception\InvalidPortException;
use Swoole\Coroutine\Http\Client;

class HttpClientManager implements PoolManagerInterface
{

    public const IP_REGEXPR = '^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$';
    public const HOST_REGEXPR = '^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$';

    /**
     * @param string $host
     * @param int $port
     * @param bool $isSsl
     * @param array|null $settings
     * @throws InvalidParameterException
     */
    public function __construct(
        protected string $host,
        protected int $port = 80,
        protected bool $isSsl = true,
        protected array | null $settings = null
    ) {
        if (preg_match(static::IP_REGEXPR) === false && preg_match(static::HOST_REGEXPR) === false) {
            throw new InvalidParameterException('Hostname must be an ip or a string responding to RFC 1123 (' . $this->host . ')');
        }

        if ($this->port <= 0 || $this->port > 65535) {
            throw new InvalidParameterException('Port must be a numric value between 1 and 65535 (' . $this->port . ')');
        }
    }

    /**
     * Create connection
     * @return Client
     */
    public function create(): Client
    {
        $client = new Client($this->host, $this->port, $this->isSsl);
        if ($this->settings !== null) {
            $client->set($this->settings);
        }

        return $client;
    }

    /**
     * Close connection
     * @param Client $connection
     * @return void
     */
    public function close(Client $connection)
    {
        $connection->close();
    }

}