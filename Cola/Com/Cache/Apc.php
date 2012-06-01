<?php
/**
 *
 */
class Cola_Com_Cache_Apc extends Cola_Com_Cache_Abstract
{

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options=array())
    {
        if (!extension_loaded('apc')) {
            throw new Exception('The apc extension must be loaded.');
        }

        parent::__construct($options);
    }

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
        if (null === $ttl) {
            $ttl = $this->_options['ttl'];
        }
        return apc_store($key, $value, $ttl);
    }

    /**
     * Get Cache
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return apc_fetch($key);
    }

    /**
     * Delete cache
     * @param string $id
     * @return boolean
     */
    public function delete($key)
    {
        return apc_delete($key);
    }

    /**
     * clear cache
     */
    public function clear()
    {
        return apc_clear_cache('user');
    }
}