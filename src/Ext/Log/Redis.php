<?php

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

    public function write($text)
    {
        $redis = new Cola_Ext_Cache_Redis($this->config);
        return $redis->qput($this->config['queue'], $text);
    }
}