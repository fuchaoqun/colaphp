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
            'formatter' => new LineFormatter(),
        ];
    }

    public function handle($log, $context = [])
    {
        $text = $this->_config['formatter']->format($log, $context);
        $this->_handle($text);
    }

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

    abstract function _handle($text);
}