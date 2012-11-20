<?php
/**
 *
 */
class Cola_Com_Log
{
	public static function factory($config)
	{
	    $adapter = $config['adapter'];
        $class = 'Cola_Com_Log_' . ucfirst($adapter);
        return new $class($config);
	}
}