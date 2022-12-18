<?php

namespace Sebk\SmallSwoolePatterns\Manager\Contract;

interface StoredListManagerInterface
{

    public function lpush(string $listName, string $content): void;
    public function lpop(string $listName): string;
    public function rpush(string $listName, string $content): void;
    public function rpop(string $listName): string;
    public function all(string $listName): array;
    public function lrem(string $listName, string $element, int $count = 0);

}