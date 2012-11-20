<?php
/**
 *
 */
class Cola_Com_Cache_Null extends Cola_Com_Cache_Abstract
{
    /**
     * Set cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return boolean
     */
    public function set($key, $value, $ttl = null)
    {
        return true;
    }

    /**
     * Get Cache
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return false;
    }

    /**
     * Delete cache
     * @param string $id
     * @return boolean
     */
    public function delete($key)
    {
        return true;
    }

    /**
     * clear cache
     */
    public function clear()
    {
        return true;
    }
}