<?php

namespace Sebk\SmallSwoolePatterns\Test\Pool\Bean;

use PHPUnit\Framework\TestCase;
use Sebk\SmallSwoolePatterns\Pool\Bean\RateController;
use Sebk\SmallSwoolePatterns\Pool\Enum\RateBehaviour;
use Sebk\SmallSwoolePatterns\Pool\Exception\InvalidParameterException;
use Sebk\SmallSwoolePatterns\Pool\Exception\RateExeededException;

class RateControllerTest extends TestCase
{

    public function testConstructor()
    {

        $this->expectException(InvalidParameterException::class);
        new RateController(-1, 1);

        $this->expectException(InvalidParameterException::class);
        new RateController(1, -1);

        new RateController(1, 1);

    }

    public function testMinInterval()
    {

        $rateController = (new RateController(1000, 10))
            ->tick();
        $start = microtime(true);
        $rateController->tick();
        $this->assertGreaterThan(0.01, microtime(true) - $start);
        $this->assertLessThan(0.02, microtime(true) - $start);

    }

    public function testMaxTicksException()
    {

        $rateController = new RateController(1000, 10);
        $rateController->addUnitToControl('seconds', 1, RateBehaviour::exception, 2);
        $rateController->tick();
        $rateController->tick();
        $this->expectException(RateExeededException::class);
        $rateController->tick();

    }

    public function testMaxTicksWaiting()
    {

        $rateController = new RateController(1000, 10);
        $rateController->addUnitToControl('seconds', 1, RateBehaviour::waiting, 2);
        // Warmup
        $rateController->tick();
        $rateController->tick();
        $rateController->tick();
        // Wait
        $start = microtime(true);
        $rateController->tick();
        $rateController->tick();
        $this->assertGreaterThanOrEqual(1, microtime(true) - $start);

    }

}