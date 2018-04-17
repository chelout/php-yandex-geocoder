<?php

namespace Chelout\Geocoder;

use Predis\Client as RedisClient;

class Cache
{
    /**
     * @var \Predis\Client
     */
    public $redis;

    public function __construct()
    {
        $this->redis = new RedisClient;
    }

    public function set($key, $value, $seconds = 3600)
    {
        $this->redis->set($key, json_encode($value), 'EX', $seconds);
    }

    public function get($key)
    {
        $value = $this->redis->get($key);

        if (! is_null($value)) {
            return json_decode($value, true);
        }

        return false;
    }

    public function incriment($key)
    {
        return $this->redis->incr($key);
    }

    public function decrenent($key)
    {
        return $this->redis->decr($key);
    }

    public function expireat($key, $time = null)
    {
        if (is_null($time)) {
            $time = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));
        }

        return $this->redis->expireat($key, $time);
    }

    public function exists($key)
    {
        return $this->redis->exists($key);
    }
}
