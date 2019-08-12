<?php


namespace Cola\Queue;


class SimpleQueue
{
    public static function factory($adapter, $config = [])
    {
        if (is_array($adapter)) {
            $config = isset($adapter['config']) ? $adapter['config'] : [];
            $adapter = $adapter['adapter'];
        }

        return new $adapter($config);
    }
}