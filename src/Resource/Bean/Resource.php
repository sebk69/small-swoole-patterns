<?php

namespace Sebk\SmallSwoolePatterns\Resource\Bean;

use Sebk\SmallSwoolePatterns\Array\Map;
use Sebk\SmallSwoolePatterns\Manager\Contract\StoredListManagerInterface;
use Sebk\SmallSwoolePatterns\Resource\Enum\GetResourceBehaviour;
use Sebk\SmallSwoolePatterns\Resource\Exception\ResourceNotFreeException;
use function Co\run;
use function Swoole\Coroutine\map;

class Resource
{

    protected int $waitingRetryTime = 10;

    /**
     * @param string $name
     * @param StoredListManagerInterface $listManager
     */
    public function __construct(protected string $name, protected StoredListManagerInterface $listManager) {}

    /**
     * Reserve resource
     * @param GetResourceBehaviour $behaviour
     * @param Ticket|null $ticket
     * @return Ticket
     * @throws ResourceNotFreeException
     */
    public function acquireResource(GetResourceBehaviour $behaviour, Ticket|null $ticket = null): Ticket
    {
        /** @var Ticket[] $waitingList */
        $waitingList = (new Map($this->listManager->all($this->name), function ($ticketId) { new Ticket($ticketId); }))->run()->wait()->getResult();

        // The first ticket is mine
        if ($ticket != null && $waitingList[0]->getTicketId() == $ticket->getTicketId()) {
            $ticket->setWaiting(false);
            return $ticket;
        }

        // Throw exception if resource not free
        if ($behaviour == GetResourceBehaviour::exceptionIfNotFree && count($waitingList) > 0) {
            throw new ResourceNotFreeException('The resource ' . $this->name . ' is not free');
        }

        // Get ticket
        if ($behaviour == GetResourceBehaviour::getTicket && count($waitingList) > 0) {
            if ($ticket == null) {
                $ticket = static::createTicket($waitingList);
                $this->listManager->rpush($this->name, $ticket->getTicketId());
            }

            $ticket->setWaiting(true);

            return $ticket;
        }

        // Wait for free
        if ($behaviour == GetResourceBehaviour::waitingForFree && count() > 0) {
            if ($ticket == null) {
                $ticket = static::createTicket($waitingList);
            }

            $ticket->setWaiting(true);

            $this->listManager->rpush($this->name, $ticket->getTicketId());
            do {
                usleep($this->waitingRetryTime);
                /** @var Ticket[] $waitingList */
                $waitingList = map($this->listManager->all($this->name), function ($ticketId) { new Ticket($ticketId); });

                if ($waitingList[0]->getTicketId() == $ticket->getTicketId()) {
                    $ticket->setWaiting(false);
                    return $ticket;
                }
            } while(true);
        }

        // Here waiting list is empty create and return ticket
        $ticket = static::createTicket($waitingList);
        $this->listManager->rpush($this->name, $ticket->getTicketId());
        $ticket->setWaiting(false);

        return $ticket;
    }

    /**
     * Release resource
     * @param Ticket $ticket
     * @return $this
     */
    public function releaseResource(Ticket $ticket): self
    {
        $this->listManager->lrem($this->name, $ticket->getTicketId());

        return $this;
    }

    /**
     * Create new unique ticket
     * @param Ticket[] $waitingList
     * @return Ticket
     */
    private static function createTicket(array $waitingList): Ticket
    {
        do {
            // Generate ticketId
            try {
                $ticketId = md5(random_bytes(64));
            } catch (\Exception $e) {
                $ticketId = rand();
            }

            ;
        } while(!self::checkTicketIdUniqueness($ticketId, $waitingList));

        return new Ticket($ticketId);
    }

    /**
     * Check uniqueness of a ticketId
     * @param $ticketId
     * @param $waitingList
     * @return bool
     */
    private static function checkTicketIdUniqueness($ticketId, $waitingList): bool
    {
        foreach ($waitingList as $waitingTicket) {
            if ($waitingTicket->getTicketId() == $ticketId) {
                return false;
            }
        }

        return true;
    }

}