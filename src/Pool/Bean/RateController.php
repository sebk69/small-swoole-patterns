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
        
        $this->lastTick = microtime();
    }

    /**
     * Add a unit rate to control
     * @param string $name
     * @param int $unitForSecond
     * @param RateBehaviour $behaviour
     * @return void
     */
    public function addUnitToControl(string $name, int $unitForSecond, RateBehaviour $behaviour = RateBehaviour::waiting)
    {
        $this->units[$name] = new TimeUnit($unitForSecond, $behaviour);
    }

    /**
     * Perform tick
     * @return void
     * @throws RateExeededException
     */
    public function tick()
    {
        $this->waitMinimumInterval();
        
        foreach ($this->units as $unit => $timeUnit) {
            $this->tickUnit($unit);
        }
    }

    /**
     * Sleep
     * @return void
     */
    private function wait()
    {
        usleep(1);
    }

    /**
     * Wait for minimum interval
     * @return void
     */
    private function waitMinimumInterval()
    {
        if ($this->lastTick > microtime(true) - $this->minInterval) {
            $this->wait();
        }

        $this->lastTick = microtime(true);
    }

    /**
     * Tick unit item
     * @param string $unit
     * @return void
     * @throws RateExeededException
     */
    private function tickUnit(string $unit): void
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
                        usleep(1);
                    }
                    break;
                case RateBehaviour::exception:
                    throw new RateExeededException('The seconds maximum rate has been exeeded');
            }
        }
    }

}