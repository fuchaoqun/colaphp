<?php
/**
 *
 */
class Cola_Com_Cache_Eaccelerator extends Cola_Com_Cache_Abstract
{
    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options=array())
    {
        if (!extension_loaded('eaccelerator')) {
            throw new Exception('The eAccelerator extension must be loaded.');
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
        return eaccelerator_put($key, $value, $ttl);
    }

    /**
     * Get Cache
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return eaccelerator_get($key);
    }

    /**
     * Delete cache
     * @param string $id
     * @return boolean
     */
    public function delete($key)
    {
        return eaccelerator_rm($key);
    }

    /**
     * clear cache
     */
    public function clear()
    {
        return eaccelerator_clean();
    }
}