<?php

namespace Sebk\SmallSwoolePatterns\Resource;

use Sebk\SmallSwoolePatterns\Manager\Contract\StoredListManagerInterface;
use Sebk\SmallSwoolePatterns\Pool\Pool;

class ResourceFactory
{

    /** @var Resource[] $resources */
    public array $resources = [];

    /**
     * @param array $config
     * @param StoredListManagerInterface $storedListManager
     */
    public function __construct(array $config, StoredListManagerInterface $storedListManager)
    {
        foreach ($config as $resourceName => $resourceConfig) {
            $this->resources[$resourceName] = new Resource($resourceName, $storedListManager);
        }
    }

    /**
     * Get resource
     * @param string $resourceName
     * @return Resource
     */
    public function get(string $resourceName): Resource
    {
        return $this->resources[$resourceName];
    }

}