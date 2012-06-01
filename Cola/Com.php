<?php
/**
 *
 */

class Cola_Com
{
    public static function db($config)
    {
        return Cola_Com_Db::factory($config);
    }

    public static function cache($name = null)
    {
        if (is_array($name)) {
            return Cola_Com_Cache::factory($name);
        }

        if (empty($name)) {
            $name = '_cache';
        }

        if ($cache = Cola::reg($name)) return $cache;

        $config = (array)Cola::config()->get($name);
        $cache = Cola_Com_Cache::factory($config);
        Cola::reg($name, $cache);
        return $cache;
    }

    public static function log($config)
    {
        return Cola_Com_Log::factory($config);
    }

    /**
     * Dynamic get Component
     *
     * @param string $key
     */
    public function __get($key)
    {
        $className = 'Cola_Com_' . ucfirst($key);
        if (Cola::loadClass($className)) {
            return $this->$key = new $className();
        } else {
            throw new Cola_Exception("No component like $key defined");
        }
    }
}