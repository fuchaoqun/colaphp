<?php
/**
 *
 */
class Cola_Com_Queue
{
	public static function factory($config)
	{
	    extract($config);
        $class = 'Cola_Com_Queue_' . ucfirst($adapter);
        return new $class($params);
	}
}