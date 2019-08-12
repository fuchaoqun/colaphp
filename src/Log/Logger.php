<?php

namespace Cola\Log;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use LogicException;

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

    protected $_levels = [
        self::DEBUG     => 'DEBUG',
        self::INFO      => 'INFO',
        self::NOTICE    => 'NOTICE',
        self::WARNING   => 'WARNING',
        self::ERROR     => 'ERROR',
        self::CRITICAL  => 'CRITICAL',
        self::ALERT     => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    ];

    protected $_config = [];

    protected $_handlers = [];

    public function __construct($config = [])
    {
        $this->_config = $config + [
            'channel' => '_Cola_log',
            'handlers' => [],
        ];

        foreach ($this->_config['handlers'] as $row) {
            $adapter = $row['adapter'];
            $this->_handlers[] = new $adapter($row['config']);
        }
    }

    public function pushHandler($handler)
    {
        array_unshift($this->_config['handlers'], $handler);
        return $this;
    }

    public function popHandler()
    {
        if (!$this->_config['handlers']) {
            throw new LogicException('You tried to pop from an empty handler stack.');
        }
        return array_shift($this->_config['handlers']);
    }


    /**
     * @param $level
     * @param $log
     * @param array $context
     * @throws Exception
     */
    public function log($level, $log, $context = [])
    {
        $microTime = microtime(true);
        $context += [
            'channel' => $this->_config['channel'],
            'level' => $level,
            'levelName' => $this->_levels[$level],
            'microTime' => $microTime,
            'time' => intval($microTime),
        ];

        foreach ($this->_handlers as $handler) {
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

    /**
     * @return array
     */
    public function getLevels()
    {
        return $this->_levels;
    }

    /**
     * @param array $levels
     */
    public function setLevels($levels)
    {
        $this->_levels = $levels;
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