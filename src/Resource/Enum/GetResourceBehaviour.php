<?php

namespace Sebk\SmallSwoolePatterns\Resource\Enum;

enum GetResourceBehaviour
{
    case waitingForFree;
    case exceptionIfNotFree;
    case getTicket;
}
