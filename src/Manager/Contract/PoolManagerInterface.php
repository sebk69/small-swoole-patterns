<?php

/*
 *  This file is a part of small-swoole-patterns
 *  Copyright 2022 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace Sebk\SmallSwoolePatterns\Manager\Contract;

interface PoolManagerInterface
{

    public function create();
    public function close(mixed $connection);

}