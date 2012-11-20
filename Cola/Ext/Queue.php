<?php
/**
 *
 */
class Cola_Com_Queue
{
	public static function factory($config)
	{
	    $adapter = $config['adapter'];
        $class = 'Cola_Com_Queue_' . ucfirst($adapter);
        return new $class($config);
	}
}