<?php

namespace Sebk\SmallSwoolePatterns\Resource\Bean;

class Ticket
{

    // True if ticket is waiting for resource
    protected bool $waiting = true;

    /**
     * @param int $ticketId
     */
    public function __construct(protected int $ticketId) {}

    /**
     * @return int
     */
    public function getTicketId(): int
    {
        return $this->ticketId;
    }

    /**
     * @return bool
     */
    public function isWaiting(): bool
    {
        return $this->waiting;
    }

    /**
     * @param bool $waiting
     */
    public function setWaiting(bool $waiting): void
    {
        $this->waiting = $waiting;
    }

}