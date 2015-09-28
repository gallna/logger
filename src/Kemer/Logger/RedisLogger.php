<?php
namespace Kemer\Logger;

class RedisLogger
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {

        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
        $this->redis->subscribe(['server-1'], [$this, "redisCallback"]);
    }

    public function redisCallback($redis, $chan, $msg)
    {
        var_dump(func_get_args());
    }
}
