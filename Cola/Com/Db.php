<?php
/**
 *
 */
class Cola_Com_Db
{
    public static function factory($config)
    {
        $config += array('masterslave' => false);

        if ($config['masterslave']) {
            return new Cola_Com_Db_Masterslave($config);
        }

        extract($config);
        $class = 'Cola_Com_Db_' . ucfirst($adapter);
        return new $class($params);
    }
}