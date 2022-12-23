<?php

/*
 *  This file is a part of small-swoole-patterns
 *  Copyright 2022 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace Sebk\SmallSwoolePatterns\Pool\Bean;

use Sebk\SmallSwoolePatterns\Pool\Enum\RateBehaviour;
use Sebk\SmallSwoolePatterns\Pool\Exception\InvalidParameterException;
use Sebk\SmallSwoolePatterns\Pool\Exception\RateExeededException;

class RateController
{

    protected float $lastTick; // Microtime
    /** @var TimeUnit[] */
    protected array $units = [];

    public function __construct(
        protected int $minInterval = 1, // in µs
        protected int $waitTime = 1, // in µs
    ) {
        if ($this->waitTime <= 0) {
            throw new InvalidParameterException('The minimum interval must be superior to 0');
        }
        
        if ($this->minInterval <= 0) {
            throw new InvalidParameterException('The wait time must be superior to 0');
        }
        
        $this->lastTick = microtime(true);
    }

    /**
     * Add a unit rate to control
     * @param string $name
     * @param int $unitForSecond
     * @param RateBehaviour $behaviour
     * @param int $maxTicks
     * @return $this
     * @throws InvalidParameterException
     */
    public function addUnitToControl(string $name, int $unitForSecond, RateBehaviour $behaviour, int $maxTicks): self
    {
        $this->units[$name] = new TimeUnit($unitForSecond, $behaviour, $maxTicks);

        return $this;
    }

    /**
     * Perform tick
     * @return $this
     * @throws RateExeededException
     */
    public function tick(): self
    {
        $this->waitMinimumInterval();
        
        foreach ($this->units as $unit => $timeUnit) {
            $this->tickUnit($unit);
        }

        return $this;
    }

    /**
     * Sleep
     * @return $this
     */
    private function wait(): self
    {
        usleep($this->waitTime);

        return $this;
    }

    /**
     * Wait for minimum interval
     * @return $this
     */
    private function waitMinimumInterval()
    {
        while (microtime(true) - $this->lastTick < $this->minInterval / 100000) {
            $this->wait();
        }

        $this->lastTick = microtime(true);

        return $this;
    }

    /**
     * Tick unit item
     * @param string $unit
     * @return $this
     * @throws RateExeededException
     */
    private function tickUnit(string $unit): self
    {
        // Has changed ?
        $this->units[$unit]->hasChanged();

        // Increment
        $this->units[$unit]->addTick();

        // Check rate
        while ($this->units[$unit]->getNumTicks() > $this->units[$unit]->getMaxTicks()) {
            switch ($this->units[$unit]->getBehaviour()) {
                case RateBehaviour::waiting:
                    // Check times change
                    while(!$this->units[$unit]->hasChanged(true)) {
                        usleep($this->minInterval);
                    }
                    break;
                case RateBehaviour::exception:
                    throw new RateExeededException('The seconds maximum rate has been exeeded');
            }
        }

        return $this;
    }

}