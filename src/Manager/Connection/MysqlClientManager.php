<?php

namespace Sebk\SmallSwoolePatterns\Manager\Connection;

use Sebk\SmallSwoolePatterns\Manager\Contract\PoolManagerInterface;

class MysqlClientManager implements PoolManagerInterface
{
    public function __construct(
        protected string $database,
        protected string $host,
        protected string $encoding,
        protected string $user,
        protected string $password,
    ) {}

    /**
     * Create mysql client
     * @return \PDO
     */
    public function create(): \PDO
    {
        return new \PDO(
            "mysql:dbname=$this->database;host=$this->host;charset=$this->encoding",
            $this->user,
            $this->password,
            [\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION]
        );
    }

    /**
     * @param \PDO $connection
     * @return void
     */
    public function close(mixed $connection)
    {
        unset($connection);
    }

}