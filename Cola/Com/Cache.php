<?php
/**
 *
 */
class Cola_Com_Cache
{
	public static function factory($config)
	{
	    extract($config);
        $class = 'Cola_Com_Cache_' . ucfirst($adapter);
        return new $class($params);
	}
}