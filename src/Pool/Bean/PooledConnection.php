<?php

namespace Sebk\SmallSwoolePatterns\Pool\Bean;

use Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionBusyException;

class PooledConnection
{

    public function __construct(protected mixed $connection, protected bool $isBusy = false) {}

    /**
     * Lock connection and return it
     * @return mixed
     */
    public function getConnection(): mixed
    {
        return $this->connection;
    }

    /**
     * Lock connection
     * @return $this
     * @throws PooledConnectionBusyException
     */
    public function lock(): self
    {
        if ($this->isBusy) {
            throw new PooledConnectionBusyException('Connection is busy');
        }

        $this->isBusy = true;

        return $this;
    }

    /**
     * Unlock connection
     * @return $this
     */
    public function unlock(): self
    {
        $this->isBusy = false;

        return $this;
    }
    
    public function isBusy(): bool
    {
        return $this->isBusy;
    }

}