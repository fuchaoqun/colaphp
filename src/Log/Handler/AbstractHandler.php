<?php

namespace Cola\Log\Handler;

use Cola\Log\Formatter\LineFormatter;
use Cola\Log\Logger;

abstract class AbstractHandler
{
    protected $_config = [];

    public function __construct($config = [])
    {
        $this->_config = $config + $this->_config + [
            'level' => Logger::DEBUG,
            'bubble' => true,
        ];

        if (is_array($this->_config['formatter'])) {
            $adapter = $this->_config['formatter']['adapter'];
            $formatterConfig =  $this->_config['formatter']['config'];
            $this->_config['formatter'] = new $adapter($formatterConfig);
        }
    }

    abstract public function handle($log, $context = []);

    public function shouldHandle($level)
    {
        return $level >= $this->_config['level'];
    }

    public function isBubble()
    {
        return $this->_config['bubble'];
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }
}