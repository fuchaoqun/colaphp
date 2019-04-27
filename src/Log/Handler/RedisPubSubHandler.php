<?php

namespace Cola\Log\Handler;

class Cola_Ext_Log_Redis extends Cola_Ext_Log_Abstract
{
    public $config = array(
        'persistent' => true,
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'timeout'    => 3,
        'options'    => array(),
        'queue'      => '_cola_redis_queue'
    );

    protected $_redis = null;

    public function write($text)
    {
        if (is_null($this->_redis)) {
            $factory = $this->config + ['adapter' => 'RedisAdapter'];
            $this->_redis = \Cola\Cache\SimpleCache::factory($factory);
        }

        return $redis->qput($this->config['queue'], $text);
    }
}