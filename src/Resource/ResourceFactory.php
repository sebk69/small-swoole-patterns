<?php

namespace Sebk\SmallSwoolePatterns\Resource;

use Sebk\SmallSwoolePatterns\Manager\Contract\StoredListManagerInterface;
use Sebk\SmallSwoolePatterns\Resource\Bean\Resource;

class ResourceFactory
{

    /** @var Resource[] $resources */
    public array $resources = [];

    /**
     * @param array $config
     * @param StoredListManagerInterface $storedListManager
     */
    public function __construct(array $config, protected StoredListManagerInterface $storedListManager)
    {
        foreach ($config as $resourceName) {
            $this->resources[$resourceName] = new Resource($resourceName, $this->storedListManager);
        }
    }

    /**
     * Get resource
     * @param string $resourceName
     * @return Resource
     */
    public function get(string $resourceName): Resource
    {
        if (!array_key_exists($resourceName, $this->resources)) {
            return $this->resources[$resourceName] = new Resource($resourceName, $this->storedListManager);
        }

        return $this->resources[$resourceName];
    }

}