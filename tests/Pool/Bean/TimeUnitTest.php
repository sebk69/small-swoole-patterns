<?php

namespace Sebk\SmallSwoolePatterns\Test\Pool\Bean;

use PHPUnit\Framework\TestCase;
use Sebk\SmallSwoolePatterns\Pool\Bean\TimeUnit;
use Sebk\SmallSwoolePatterns\Pool\Enum\RateBehaviour;

class TimeUnitTest extends TestCase
{

    public function testGetters()
    {

        $timeUnit = new TimeUnit(10, RateBehaviour::waiting, 15);

        self::assertEquals(10, $timeUnit->getUnitsForSeconds());
        self::assertEquals(RateBehaviour::waiting, $timeUnit->getBehaviour());
        self::assertEquals(15, $timeUnit->getMaxTicks());

        $timeUnit->addTick();
        self::assertEquals(1, $timeUnit->getNumTicks());

        self::assertIsInt($timeUnit->getLastTick());

        self::assertIsInt($timeUnit->getTime());

    }

    public function testHasChanged()
    {

        $timeUnit = new TimeUnit(1, RateBehaviour::exception, 1);

        $timeUnit->addTick();
        $this->assertFalse($timeUnit->hasChanged());

        sleep(2);
        $this->assertTrue($timeUnit->hasChanged());

    }

    public function testSetters()
    {

        $timeUnit = new TimeUnit(1, RateBehaviour::exception, 1);
        $this->assertEquals(3, $timeUnit->setNumTicks(3)->getNumTicks());
        $this->assertIsInt($timeUnit->setLastTick()->getLastTick());
        $this->assertEquals(2, $timeUnit->setUnitsForSeconds(2)->getUnitsForSeconds());
        $this->assertEquals(5, $timeUnit->setMaxTicks(5)->getMaxTicks());
        $this->assertEquals(RateBehaviour::waiting, $timeUnit->setBehaviour(RateBehaviour::waiting)->getBehaviour());

    }

}