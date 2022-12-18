<?php

namespace Sebk\SmallSwoolePatterns\Manager\StoredListManager;

use Predis\Client;
use Sebk\SmallSwoolePatterns\Manager\Connection\PRedisClientManager;
use Sebk\SmallSwoolePatterns\Manager\Contract\StoredListManagerInterface;
use Sebk\SmallSwoolePatterns\Manager\Exception\ManagerException;
use Sebk\SmallSwoolePatterns\Pool\Pool;

class PRedisStoredListManager implements StoredListManagerInterface
{

    /**
     * @param Pool $connectionPool
     * @throws ManagerException
     */
    public function __construct(protected Pool $connectionPool) {
        if (!$this->connectionPool->getManager() instanceof PRedisClientManager) {
            throw new ManagerException('Wrong connection manager (' . $this->connectionPool->getManager()::class . '). Expecting ' . PRedisClientManager::class . ' instance');
        }
    }

    /**
     * Push $content in left side of $listName list
     * @param string $listName
     * @param string $content
     * @return void
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\ConnectionNotInPoolException
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionBusyException
     */
    public function lpush(string $listName, string $content): void
    {
        $client = $this->getClient();
        $client->lpush($listName, $content);
        $this->releaseClient($client);
    }

    /**
     * Pop first element from left
     * @param string $listName
     * @return string
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\ConnectionNotInPoolException
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionBusyException
     */
    public function lpop(string $listName): string
    {
        $client = $this->getClient();
        $result = $client->lpop($listName);
        $this->releaseClient($client);

        return $result;
    }

    /**
     * Push $content in right side of $listName list
     * @param string $listName
     * @param string $content
     * @return void
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\ConnectionNotInPoolException
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionBusyException
     */
    public function rpush(string $listName, string $content): void
    {
        $client = $this->getClient();
        $client->rpush($listName, $content);
        $this->releaseClient($client);
    }

    /**
     * Pop first element from right
     * @param string $listName
     * @return string
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\ConnectionNotInPoolException
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionBusyException
     */
    public function rpop(string $listName): string
    {
        $client = $this->getClient();
        $result = $client->rpop($listName);
        $this->releaseClient($client);

        return $result;
    }

    /**
     * Get all elements from $listName list
     * @param string $listName
     * @return string
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\ConnectionNotInPoolException
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionBusyException
     */
    public function all(string $listName): array
    {
        $client = $this->getClient();
        $result = $client->lrange($listName, 0, -1);
        $this->releaseClient($client);

        return $result;
    }

    /**
     * Remove $count elements $element from $listName list
     * @param string $listName
     * @param string $element
     * @param int $count
     * @return int
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\ConnectionNotInPoolException
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionBusyException
     */
    public function lrem(string $listName, string $element, int $count = 0)
    {
        $client = $this->getClient();
        $result = $client->lrem($listName, $count, $element);
        $this->releaseClient($client);

        return $result;
    }

    public function reset(string $listName)
    {
        $client = $this->getClient();
        $client->del($listName);
        $this->releaseClient($client);
    }

    /**
     * Get a redis client
     * @return Client
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionBusyException
     */
    protected function getClient(): Client
    {
        return $this->connectionPool->get();
    }

    /**
     * Release a redis client
     * @param Client $client
     * @return $this
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\ConnectionNotInPoolException
     * @throws \Sebk\SmallSwoolePatterns\Pool\Exception\PooledConnectionBusyException
     */
    protected function releaseClient(Client $client): self
    {
        $this->connectionPool->put($client);

        return $this;
    }

}