<?php

/*
 *  This file is a part of small-swoole-patterns
 *  Copyright 2022 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace Sebk\SmallSwoolePatterns\Pool;

use Sebk\SmallSwoolePatterns\Pool\Bean\PooledConnection;
use Sebk\SmallSwoolePatterns\Pool\Contract\PoolManagerInterface;
use Sebk\SmallSwoolePatterns\Pool\Exception\ConnectionNotInPoolException;
use Sebk\SmallSwoolePatterns\Pool\Exception\InvalidParameterException;
use Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionBusyException;
use Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionNoneFreeException;
use Sebk\SmallSwoolePatterns\Pool\Exception\RateControlNotActivatedException;
use Sebk\SmallSwoolePatterns\Pool\Exception\RateNotActivatedException;
use Sebk\SmallSwoolePatterns\Pool\Manager\Bean\RateController;
use Swoole\Coroutine\Server\Connection;

class Pool
{

    protected RateController|null $rateController = null;
    /** @var PooledConnection[] */
    protected array $pooledConnections;

    /**
     * @param PoolManagerInterface $poolManager
     * @param int $maxConnectors
     * @throws InvalidParameterException
     */
    public function __construct(
        protected PoolManagerInterface $poolManager,
        protected int $maxConnectors,
        protected int $waitTime = 10 // In microseconds
    )
    {
        if ($this->maxConnectors <= 0) {
            throw new InvalidParameterException('Max connectors must be superior to 0');
        }

        if ($this->waitTime <= 0) {
            throw new InvalidParameterException('Wait time must be superior to 0');
        }
    }

    /**
     * Activate rate control and return rate controller
     * @param int $minInterval
     * @param int $waitTime
     * @return RateController
     * @throws Exception\InvalidParameterException
     */
    public function activateRateController(int $minInterval = 1, int $waitTime = 1): RateController
    {
        return new RateController($minInterval, $waitTime);
    }

    /**
     * Get rate controller
     * @return RateController
     * @throws RateControlNotActivatedException
     */
    public function getRateController(): RateController
    {
        if ($this->rateController === null) {
            throw new RateControlNotActivatedException('You must activate rate controller before getting rate controller');
        }

        return $this->rateController;
    }

    /**
     * Get connection
     * @return mixed
     * @throws PooledConnectionBusyException
     */
    public function get(): mixed
    {
        // Try to return first free connection
        try {
            return $this->getFirstFree()->getConnection();
        } catch (PooledConnectionNoneFreeException $e) {}

        // If max connections reached
        while (count($this->pooledConnections) >= $this->maxConnectors) {
            // Sleep
            usleep($this->waitTime);

            // And try to return first free connection
            try {
                return $this->getFirstFree()->getConnection();
            } catch (PooledConnectionNoneFreeException $e) {}
        }

        // Else create new connection
        return $this->pooledConnections[] = $this->poolManager->create();
    }

    /**
     * Release connection
     * @param mixed $connection
     * @return $this
     * @throws ConnectionNotInPoolException
     * @throws PooledConnectionBusyException
     */
    public function put(mixed $connection): Pool
    {
        foreach ($this->pooledConnections as $key => $pooledConnection) {
            if (spl_object_id($connection) == spl_object_id($pooledConnection->getConnection())) {
                $pooledConnection->unlock();
                return $this;
            }
        }

        throw new ConnectionNotInPoolException('The connection #id ' . spl_object_id($connection) . ' is not in pool');
    }

    /**
     * Get first free connection
     * @return Connection
     * @throws PooledConnectionNoneFreeException
     */
    protected function getFirstFree(): PooledConnection
    {
        foreach ($this->pooledConnections as $pooledConnection) {
            try {
                return $pooledConnection->getConnection();
            } catch (PooledConnectionBusyException $e) {}
        }

        throw new PooledConnectionNoneFreeException('No free connection');
    }

}