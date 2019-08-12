<?php


namespace Cola\Queue\Adapter;


use Cola\Cache\SimpleCache;

class RedisAdapter extends AbstractAdapter
{
    protected $_adapter = '\Cola\Cache\Adapter\RedisAdapter';
    protected $_redis;

    public function __construct($config)
    {
        $this->_config = $config + $this->_config;
        $simpleCache = SimpleCache::factory($this->_adapter, $this->_config['redis']);
        $this->_redis = $simpleCache->getConnection();
    }

    public function put($channel, $msg)
    {
        return $this->_redis->lPush($channel, $msg);
    }

    public function get($channel, $timeout = 0)
    {
        if (0 > $timeout) {
            return $this->_redis->rPop($channel);
        } else {
            $data = $this->_redis->brPop((array)$channel, $timeout);
            return isset($data[1]) ? $data[1] : null;
        }
    }
}