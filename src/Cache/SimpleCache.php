<?php

namespace Cola\Cache;

/**
 * PSR-16 SimpleCache
 */
class SimpleCache
{
    public static function factory($adapter, $config = [])
    {
        return new $adapter($config);
    }
}