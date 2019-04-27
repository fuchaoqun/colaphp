<?php

namespace Cola\Log\Formatter;

class LineFormatter
{
    public function format($log, $context)
    {
        $keys = [];
        $vals = [];
        foreach ($context as $key => $val) {
            $keys[] = "%{$key}%";
            $vals[] = $val;
        }

        return str_replace($keys, $vals, $log);
    }
}