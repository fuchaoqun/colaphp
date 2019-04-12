<?php
/**
 *
 */

abstract class Cola_Ext_Log_Abstract
{
    const EMERG   = 0;  // Emergency: system is unusable
    const ALERT   = 1;  // Alert: action must be taken immediately
    const CRIT    = 2;  // Critical: critical conditions
    const ERR     = 3;  // Error: error conditions
    const WARN    = 4;  // Warning: warning conditions
    const NOTICE  = 5;  // Notice: normal but significant condition
    const INFO    = 6;  // Informational: informational messages
    const DEBUG   = 7;  // Debug: debug messages

    public static $events = array(
        'emerg'  => 0, 'emergency' => 0,
        'crit'   => 2, 'critical'  => 2,
        'err'    => 3, 'error'     => 3,
        'warn'   => 4, 'warning'   => 4,
        'alert'  => 1, 'notice'    => 5,
        'info'   => 6, 'debug'     => 7
    );

    public $config = array();

    public function __construct($config = array())
    {
        $this->config = $config + $this->config;
    }

    public function log($event, $log, $context = array())
    {
        $event = strtolower($event);
        $context += array('event' => self::$events[$event]);
        $log = $this->format($log, $context);
        $this->write($log);
    }

    public function emergency($log, $context = array())
    {
        return $this->log('emergency', $log, $context);
    }

    public function alert($log, $context = array())
    {
        return $this->log('alert', $log, $context);
    }

    public function critical($log, $context = array())
    {
        return $this->log('critical', $log, $context);
    }

    public function error($log, $context = array())
    {
        return $this->log('error', $log, $context);
    }

    public function warning($log, $context = array())
    {
        return $this->log('warning', $log, $context);
    }

    public function notice($log, $context = array())
    {
        return $this->log('notice', $log, $context);
    }

    public function info($log, $context = array())
    {
        return $this->log('info', $log, $context);
    }

    public function debug($log, $context = array())
    {
        return $this->log('debug', $log, $context);
    }

    public function format($log, $context = array())
    {
        $keys = array();
        $vals = array();
        foreach ($variable as $key => $val) {
            $keys[] = "{{$val}";
            $vals[] = $val;
        }
        return str_replace($keys, $vals, $log);
    }

    public abstract function write($text);
}