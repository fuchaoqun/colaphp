<?php

namespace Cola\Log\Handler;

abstract class AbstractHandler
{
    public function __construct($config = [])
    {
        $this->config = $config + [
            'level' => \Cola\Log\Logger::DEBUG,
            'bubble' => true,
            'formatter' => new \Cola\Log\Formatter\LineFormatter(),
        ];
    }

    public function handle($log, $context = [])
    {
        $text = $this->config['formatter']->format($log, $context);
        $this->_handle($text);
    }

    public function shouldHandle($level)
    {
        return $level >= $this->config['level'];
    }

    public function isBubble()
    {
        return $this->config['bubble'];
    }

    abstract function _handle($text);
}