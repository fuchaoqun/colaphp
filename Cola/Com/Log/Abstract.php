<?php
/**
 *
 */

abstract class Cola_Com_Log_Abstract
{
    const EMERG   = 0;  // Emergency: system is unusable
    const ALERT   = 1;  // Alert: action must be taken immediately
    const CRIT    = 2;  // Critical: critical conditions
    const ERR     = 3;  // Error: error conditions
    const WARN    = 4;  // Warning: warning conditions
    const NOTICE  = 5;  // Notice: normal but significant condition
    const INFO    = 6;  // Informational: informational messages
    const DEBUG   = 7;  // Debug: debug messages

    protected $_options = array(
        'mode' => '0755',
        'file' => '/tmp/Cola.log',
        'format' => '%time%|%event%|%msg%'
    );

    public function __construct($options = array())
    {
        foreach ($options as $key=>$value) {
            $this->_options[$key] = $value;
        }
    }

    protected function _getTime($log = null)
    {
        return is_array($log) && isset($log['time']) ? $log['time'] : date('Y-m-d H:i:s');
    }

    protected function _getEvent($log, $default = '*')
    {
        return is_array($log) && isset($log['event']) ? $log['event'] : $default;
    }

    protected function _getMsg($log)
    {
        return is_array($log) && isset($log['msg']) ? $log['msg'] : $log;
    }

    protected function _format($log, $defaultEvent = '*')
    {
        $data = array(
            '%time%' => $this->_getTime($log),
            '%event%' => $this->_getEvent($log, $defaultEvent),
            '%msg%' => $this->_getMsg($log)
        );
        $text = str_replace(array('%time%', '%event%', '%msg%'), $data,$this->_options['format']);

        return $text;
    }

    public function log($log, $event = '*')
    {
        $this->_handler($this->_format($log, $event));
    }

    public function error($log)
    {
        $this->_handler($this->_format($log, self::ERR));
    }

    public function debug($log)
    {
        $this->_handler($this->_format($log, self::DEBUG));
    }

    protected abstract function _handler($text);
}