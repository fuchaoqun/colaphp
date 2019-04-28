<?php

namespace Cola\Log;

class Logger
{
    const EMERGENCY = 600;  // Emergency: system is unusable
    const ALERT     = 550;  // Alert: action must be taken immediately
    const CRITICAL  = 500;  // Critical: critical conditions
    const ERROR     = 400;  // Error: error conditions
    const WARNING   = 300;  // Warning: warning conditions
    const NOTICE    = 250;  // Notice: normal but significant condition
    const INFO      = 200;  // Informational: informational messages
    const DEBUG     = 100;  // Debug: debug messages

    public static $levels = [
        self::DEBUG     => 'DEBUG',
        self::INFO      => 'INFO',
        self::NOTICE    => 'NOTICE',
        self::WARNING   => 'WARNING',
        self::ERROR     => 'ERROR',
        self::CRITICAL  => 'CRITICAL',
        self::ALERT     => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    ];

    public $config = [];

    public function __construct($config = [])
    {
        $this->config = $config + [
            'channel' => '_Cola',
            'handlers' => [],
            'timezone' => new \DateTimeZone(date_default_timezone_get() ?: 'UTC'),
            'dateTimeFormat' => 'Y-m-d H:i:s'
        ];
    }

    public function pushHandler($handler)
    {
        array_unshift($this->config['handlers'], $handler);
        return $this;
    }

    public function popHandler()
    {
        if (!$this->config['handlers']) {
            throw new \LogicException('You tried to pop from an empty handler stack.');
        }
        return array_shift($this->config['handlers']);
    }


    public function log($level, $log, $context = [])
    {
        $dateTime = new \DateTimeImmutable('now', $this->config['timezone']);
        $context += [
            'channel' => $this->config['channel'],
            'level' => $level,
            'levelName' => static::$levels[$level],
            'microTime' => $dateTime->format('U.v'),
            'time' => $dateTime->format('U'),
            'dateTime' => $dateTime->format($this->config['dateTimeFormat'])
        ];

        foreach ($this->config['handlers'] as $handler) {
            if (!$handler->shouldHandle($level)) continue;
            $handler->handle($log, $context);
            if ($handler->isBubble()) break;
        }
    }

    public function emergency($log, $context = [])
    {
        return $this->log(static::EMERGENCY, $log, $context);
    }

    public function alert($log, $context = [])
    {
        return $this->log(static::ALERT, $log, $context);
    }

    public function critical($log, $context = [])
    {
        return $this->log(static::CRITICAL, $log, $context);
    }

    public function error($log, $context = [])
    {
        return $this->log(static::ERROR, $log, $context);
    }

    public function warning($log, $context = [])
    {
        return $this->log(static::WARNING, $log, $context);
    }

    public function notice($log, $context = [])
    {
        return $this->log(static::NOTICE, $log, $context);
    }

    public function info($log, $context = [])
    {
        return $this->log(static::INFO, $log, $context);
    }

    public function debug($log, $context = [])
    {
        return $this->log(static::DEBUG, $log, $context);
    }
}