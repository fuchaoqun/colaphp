<?php
/**
 *
 */
class Cola_Com_Db
{
    public static function factory($config)
    {
        $config += array('masterslave' => false, 'adapter' => 'Pdo_Mysql');

        if ($config['masterslave']) {
            return new Cola_Com_Db_Masterslave($config);
        }

        $adapter = $config['adapter'];
        $class = 'Cola_Com_Db_' . ucfirst($adapter);
        return new $class($config);
    }
}