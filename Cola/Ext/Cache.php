<?php
/**
 *
 */
class Cola_Com_Cache
{
	public static function factory($config)
	{
	    $adapter = $config['adapter'];
        $class = 'Cola_Com_Cache_' . ucfirst($adapter);
        return new $class($config);
	}
}