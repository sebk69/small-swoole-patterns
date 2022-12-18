<?php

namespace Sebk\SmallSwoolePatterns\Pool\Bean;

use Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionBusyException;

class PooledConnection
{

    public function __construct(protected mixed $connection, protected bool $isBusy = false) {}

    /**
     * Lock connection and return it
     * @return mixed
     * @throws PooledConnectionBusyException
     */
    public function getConnection(): mixed
    {
        return $this->connection;
    }

    /**
     * Lock connection
     * @return void
     * @throws PooledConnectionBusyException
     */
    public function lock(): void
    {
        if ($this->isBusy) {
            throw new PooledConnectionBusyException('Connection is busy');
        }

        $this->isBusy = true;
    }

    /**
     * Unlock connection
     * @return void
     */
    public function unlock(): void
    {
        $this->isBusy = false;
    }

}