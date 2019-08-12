<?php

namespace Cola\Log\Handler;

use Cola\Cache\SimpleCache;

class RedisPubSubHandler extends AbstractHandler
{
    protected $_adapter = '\Cola\Cache\Adapter\RedisAdapter';
    protected $_redis;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $simpleCache = SimpleCache::factory($this->_adapter, $this->_config['redis']);
        $this->_redis = $simpleCache->getConnection();
    }

    public function handle($log, $context = [])
    {
        $text = $this->_config['formatter']->format($log, $context);
        return $this->_redis->publish($context['channel'], $text);
    }
}