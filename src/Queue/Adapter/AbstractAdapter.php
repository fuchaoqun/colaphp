<?php


namespace Cola\Queue\Adapter;


abstract class AbstractAdapter
{
    protected $_config = [];

    abstract public function put($channel, $msg);
    abstract public function get($channel, $timeout = 0);

    public function getConfig()
    {
        return $this->_config;
    }
}