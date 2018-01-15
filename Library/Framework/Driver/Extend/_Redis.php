<?php
/**
 * redis操作类
 */

namespace FF\Framework\Driver\Extend;

use FF\Framework\Common\Code;

class _Redis
{
    private $redis;

    private $host;

    private $port;

    private $auth;

    public function __construct($host, $port, $auth = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->auth = $auth;
    }

    /**
     * @return \Redis
     * @throws \Exception
     */
    public function getInstance()
    {
        if (!$this->redis) {
            $redis = new \Redis();
            $connected = $redis->connect($this->host, $this->port, 0.5);
            if (!$connected) {
                throw new \Exception('Failed to connect redis [' . $this->host . ':' . $this->port . ']', Code::REDIS_CONNECT_FAILED);
            }
            if ($this->auth && !$redis->auth($this->auth)) {
                throw new \Exception('Failed to auth redis connection', Code::REDIS_AUTH_FAILED);
            }
            $this->redis = $redis;
        }

        return $this->redis;
    }

    public function __call($method, $args)
    {
        if (!class_exists('Redis', false)) {
            return false;
        }
        return call_user_func_array(array($this->getInstance(), $method), $args);
    }
}