<?php

namespace Cola\Log\Handler;

use Cola\Cache\SimpleCache;

class Cola_Ext_Log_Redis extends Cola_Ext_Log_Abstract
{
    protected $_config = [
        'queue' => '_cola_log_queue'
    ];

    protected $_redis = null;

    public function write($text)
    {
        if (is_null($this->_redis)) {
            $this->_redis = SimpleCache::factory('RedisAdapter', $this->_config);
        }

        return $this->_redis->qput($this->_config['queue'], $text);
    }
}