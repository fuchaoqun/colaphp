<?php

namespace Cola\Log\Formatter;

class LineFormatter
{
    public function format($log, $context)
    {
        $keys = [];
        $values = [];
        foreach ($context as $key => $val) {
            $keys[] = "%{$key}%";
            $values[] = $val;
        }

        return str_replace($keys, $values, $log);
    }
}