<?php

namespace Cola\Log\Formatter;


class LineFormatter extends AbstractFormatter
{
    protected $_config = [
        'format' => '%dateTime%|%levelName%|%message%',
        'dateTimeFormat' => 'Y-m-d H:i:s'
    ];

    public function format($message, $context = [])
    {
        $this->_updateContext($context, $message);

        $keys = [];
        $values = [];
        foreach ($context as $key => $val) {
            $keys[] = "%{$key}%";
            $values[] = $val;
        }

        return str_replace($keys, $values, $this->_config['format']);
    }
}