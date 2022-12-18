<?php

/*
 *  This file is a part of small-swoole-patterns
 *  Copyright 2022 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace Sebk\SmallSwoolePatterns\Pool\Bean;

use Sebk\SmallSwoolePatterns\Pool\Enum\RateBehaviour;
use Sebk\SmallSwoolePatterns\Pool\Exception\InvalidParameterException;

class TimeUnit
{

    protected int $lastTick;
    protected int $numTicks = 0;
    
    public function __construct(
        protected int $unitsForSeconds,
        protected RateBehaviour $behaviour,
        protected int $maxTicks,
    )
    {
        if ($this->unitsForSeconds <= 0) {
            throw new InvalidParameterException('Units for second must be superior to 0');
        }

        if ($this->maxTicks <= 0) {
            throw new InvalidParameterException('Max ticks must be superior to 0');
        }

        $this->setLastTick();
    }

    /**
     * @return RateBehaviour
     */
    public function getBehaviour(): RateBehaviour
    {
        return $this->behaviour;
    }

    /**
     * @param RateBehaviour $behaviour
     * @return TimeUnit
     */
    public function setBehaviour(RateBehaviour $behaviour): TimeUnit
    {
        $this->behaviour = $behaviour;
        return $this;
    }

    /**
     * @return int
     */
    public function getUnitsForSeconds(): int
    {
        return $this->unitsForSeconds;
    }

    /**
     * @param int $unitsForSeconds
     * @return TimeUnit
     */
    public function setUnitsForSeconds(int $unitsForSeconds): TimeUnit
    {
        $this->unitsForSeconds = $unitsForSeconds;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxTicks(): int
    {
        return $this->maxTicks;
    }

    /**
     * @param int $maxTicks
     * @return TimeUnit
     */
    public function setMaxTicks(int $maxTicks): TimeUnit
    {
        $this->maxTicks = $maxTicks;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumTicks(): int
    {
        return $this->numTicks;
    }

    /**
     * @param int $numTicks
     * @return TimeUnit
     */
    public function setNumTicks(int $numTicks): TimeUnit
    {
        $this->numTicks = $numTicks;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastTick(): int
    {
        return $this->lastTick;
    }

    /**
     * @param int $timestamp
     * @return TimeUnit
     */
    public function setLastTick(): TimeUnit
    {
        $this->lastTick = $this->getTime();
        return $this;
    }

    /**
     * Return current timestamp in unit
     * @return int
     */
    public function getTime(): int
    {
        return floor(time() / $this->unitsForSeconds);
    }

    /**
     * Add a tick
     * @return TimeUnit
     */
    public function addTick(): TimeUnit
    {
        $this->numTicks++;
    }

    /**
     * Return true and reinit unit on change. Return false if not.
     * @param bool $addTickOnChange
     * @return bool
     */
    public function hasChanged(bool $addTickOnChange = false): bool
    {
        if ($this->units[$unit]->getLastTick() != $this->units[$unit]->getTime()) {
            $this->units[$unit]->setNumTicks(0);
            $this->units[$unit]->setLastTick();
            
            if ($addTickOnChange) {
                $this->addTick();
            }
            
            return true;
        }
        
        return false;
    }

}