<?php
/*
 *  This file is a part of small-swoole-patterns
 *  Copyright 2022 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace Sebk\SmallSwoolePatterns\Observable;

use Sebk\SmallSwoolePatterns\Array\Map;
use Sebk\SmallSwoolePatterns\Observable\Exception\ObservableAlreadyRanException;
use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;

class Observable
{

    /** @var \Closure method to observe */
    protected \Closure $method;

    /** @var array listeners called on success */
    protected array $leftListeners = [];
    /** @var array listeners called on exception */
    protected array $rightListenners = [];

    // Wait groupes
    private WaitGroup|null $methodWaitGroup = null;
    private WaitGroup|null $listenersWaitGroup = null;

    // Can run only once
    private bool $ran = false;

    /**
     * @param \Closure $method
     */
    public function __construct($method)
    {
        $this->method = $method;
    }

    /**
     * Add subscriber
     * @param $left
     * @param $right
     * @return $this
     */
    public function subscribe($left, $right = null): self
    {
        $this->leftListeners[] = $left;
        if ($right != null) {
            $this->rightListenners[] = $right;
        }

        return $this;
    }

    /**
     * Run method with params and call subscribers
     * @param ...$params
     * @return $this
     * @throws ObservableAlreadyRanException
     */
    public function run(...$params): self
    {
        // Can run only once
        if ($this->ran) {
            throw new ObservableAlreadyRanException('Observable can run only once');
        }
        $this->ran = true;

        // Inside coroutine...
        Coroutine::create(function(...$params) {
            // Create wait group
            if ($this->methodWaitGroup === null) {
                $this->methodWaitGroup = new WaitGroup(1);
            } else {
                $this->methodWaitGroup->add(1);
            }

            // default subscribers is left
            $subscribersDirection = 'left';

            // Call method
            try {
                $method = $this->method;
                $data = $method(...$params);
            } catch(\Exception $e) {
                // Redirect exception to right listeners
                $subscribersDirection = 'right';
                $this->listenersWaitGroup = new WaitGroup(count($this->rightListenners));
                (new Map($this->rightListenners, function(\Closure $closure) use ($e) {
                    $closure($e);
                }, $this->listenersWaitGroup))->run();
            }

            // Map left listeners
            if ($subscribersDirection == 'left') {
                $this->listenersWaitGroup = new WaitGroup(count($this->leftListeners));
                (new Map($this->leftListeners, function(\Closure $closure) use ($data) {
                    $closure($data);
                }, $this->listenersWaitGroup))->run();
            }

            // Unlock method waitGroup
            $this->methodWaitGroup->done();
        }, ...$params);

        return $this;
    }

    /**
     * Wait all done
     * @return void
     */
    public function wait()
    {
        $this->methodWaitGroup->wait();
        $this->listenersWaitGroup->wait();
    }
}
