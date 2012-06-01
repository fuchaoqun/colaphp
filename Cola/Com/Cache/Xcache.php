<?php
/**
 *
 */
class Cola_Com_Cache_Xcache extends Cola_Com_Cache_Abstract
{
    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options=array())
    {
        if (!extension_loaded('xcache')) {
            throw new Exception('The xcache extension must be loaded.');
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
        return xcache_set($key, $value, $ttl);
    }

    /**
     * Get Cache
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return xcache_get($key);
    }

    /**
     * Delete cache
     * @param string $id
     * @return boolean
     */
    public function delete($key)
    {
        return xcache_unset($key);
    }

    /**
     * clear cache
     */
    public function clear()
    {
        $backup = array();
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $backup['PHP_AUTH_USER'] = $_SERVER['PHP_AUTH_USER'];
        }
        if (isset($_SERVER['PHP_AUTH_PW'])) {
            $backup['PHP_AUTH_PW'] = $_SERVER['PHP_AUTH_PW'];
        }
        if ($this->_options['user']) {
            $_SERVER['PHP_AUTH_USER'] = $this->_options['user'];
        }
        if ($this->_options['password']) {
            $_SERVER['PHP_AUTH_PW'] = $this->_options['password'];
        }
        xcache_clear_cache(XC_TYPE_VAR, 0);
        if (isset($backup['PHP_AUTH_USER'])) {
            $_SERVER['PHP_AUTH_USER'] = $backup['PHP_AUTH_USER'];
            $_SERVER['PHP_AUTH_PW'] = $backup['PHP_AUTH_PW'];
        }
        return true;
    }
}