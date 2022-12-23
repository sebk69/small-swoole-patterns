<?php

namespace Sebk\SmallSwoolePatterns\Test\Resource\Bean;

use PHPUnit\Framework\TestCase;
use Sebk\SmallSwoolePatterns\Resource\Bean\Ticket;

class TicketTest extends TestCase
{

    public function testBasic()
    {

        $ticket = new Ticket(1);
        $this->assertEquals(1, $ticket->getTicketId());

        $this->assertTrue($ticket->isWaiting());
        $this->assertInstanceOf(Ticket::class, $ticket->setWaiting(false));
        $this->assertFalse($ticket->isWaiting());
        $this->assertInstanceOf(Ticket::class, $ticket->setWaiting(true));
        $this->assertTrue($ticket->isWaiting());

    }

}