<?php


namespace Cola\Log\Formatter;


use DateTime;
use DateTimeZone;

abstract class AbstractFormatter
{
    protected $_config;

    public function __construct($config = [])
    {
        $this->_config = $config + $this->_config;

        if (empty($this->_config['timezone'])) {
            $this->_config['timezone'] = new DateTimeZone(date_default_timezone_get() ?: 'UTC');
        }
    }

    protected function _updateContext(&$context, $message)
    {
        $dt = DateTime::createFromFormat('U.u', $context['microTime'], $this->_config['timezone']);
        $context += [
            'message' => $message,
            'dateTime' => $dt->format('Y-m-d H:i:s'),
        ];
    }

    abstract public function format($message, $context = []);
}