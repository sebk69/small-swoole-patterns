<?php

namespace Sebk\SmallSwoolePatterns\Test\Observable;

use PHPUnit\Framework\TestCase;
use Sebk\SmallSwoolePatterns\Observable\Exception\ObservableAlreadyRanException;
use Sebk\SmallSwoolePatterns\Observable\Observable;

class ObservableTest extends TestCase
{

    public function testSingleObservable(): void
    {

        (new Observable(function () {
            return true;
        }))->subscribe(function (bool $result) {
            $this->assertTrue($result);
        })->run()->wait();

    }

    public function testExceptionOnTwoRuns(): void
    {

        $this->expectException(ObservableAlreadyRanException::class);

        (new Observable(function () {
            return true;
        }))->subscribe(function (bool $result) {
            $this->assertTrue($result);
        })->run()->run()->wait();

    }

    public function testRightMethodCall(): void
    {

        $message = "test ok";

        (new Observable(function () use($message) {
            throw new \Exception($message);
        }))->subscribe(function (bool $result) {
        }, function (\Exception $exception) use ($message) {
            $this->assertEquals($message, $exception->getMessage());
        })->run()->wait();

    }

}