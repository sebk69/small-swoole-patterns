<?php

namespace Sebk\SmallSwoolePatterns\Test\Array;

use PHPUnit\Framework\TestCase;
use Sebk\SmallSwoolePatterns\Array\Map;
use function Co\run;

class MapTest extends TestCase
{

    public function testSimpleUsage()
    {
        $array = (new Map([1, 2, 3, 4], function ($number) {
            return $number;
        }))->run()
            ->wait()
            ->getResult()
        ;

        $this->assertIsArray($array);
        $this->assertEquals(4, count($array));
        foreach ($array as $i => $value) {
            $this->assertEquals($i + 1, $value);
        }
    }

}