<?php
/*
 *  This file is a part of small-swoole-patterns
 *  Copyright 2022 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace Sebk\SmallSwoolePatterns\Array;

use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Sebk\SmallSwoolePatterns\Compatibility\OpenSwooleWaitGroup;

class Map
{

    // Gather result
    protected array $result = [];

    /**
     * @param array $data
     * @param \Closure $closure
     * @param WaitGroup|OpenSwooleWaitGroup|null $waitGroup
     */
    public function __construct(protected array $data, protected \Closure $closure, protected WaitGroup|OpenSwooleWaitGroup|null $waitGroup = null) {}

    /**
     * Get waitGroup
     * @return WaitGroup|OpenSwooleWaitGroup
     */
    public function getWaitGroup(): WaitGroup|OpenSwooleWaitGroup
    {
        return $this->waitGroup;
    }

    /**
     * Run
     * @return $this
     */
    public function run(): self
    {
        if ($this->waitGroup == null) {
            if (class_exists(WaitGroup::class)) {
                $this->waitGroup = new WaitGroup(count($this->data));
            } else {
                $this->waitGroup = new OpenSwooleWaitGroup(count($this->data));
            }
        }

        foreach ($this->data as $id => $elem) {
            Coroutine::create(function () use ($id, $elem): void {
                $fn = $this->closure;

                $this->result[$id] = null;
                $this->result[$id] = $fn($elem);
                $this->waitGroup->done();
            });
        }

        return $this;
    }

    /** Get result */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * Wait run finished
     * @return $this
     */
    public function wait(): self
    {
        $this->waitGroup->wait();

        return $this;
    }

}