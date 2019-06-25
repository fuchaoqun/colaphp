<?php

namespace Cola\Cache\Adapter;

use Memcached;

class MemcachedAdapter extends AbstractAdapter
{
    public function __construct($config = [])
    {
        parent::__construct($config);

        if (isset($this->_config['persistent'])) {
            $this->_connection = new Memcached($this->_config['persistent']);
        } else {
            $this->_connection = new Memcached();
        }

        $this->_connection->addServers($this->_config['servers']);
    }

    /**
     * Set cache
     *
     * @param string $key
     * @param $val
     * @param int $ttl
     * @return boolean
     */
    public function set($key, $val, $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->_config['ttl'];
        }

        return $this->_connection->set($key, $val, $ttl);
    }

    public function setMultiple($values, $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->_config['ttl'];
        }

        return $this->_connection->setMulti($values, $ttl);
    }

    /**
     * Get Cache Data
     *
     * @param string $key
     * @param mixed $default
     * @return string|null
     */
    public function get($key, $default = null)
    {
        $rps = $this->_connection->get($key);
        return (false === $rps) ? $default : $rps;
    }

    public function getMultiple($keys, $default = null)
    {
        $rps = $this->_connection->getMulti($keys);
        empty($rps) && $rps = [];
        foreach ($keys as $key) {
            (!isset($rps[$key])) && $rps[$key] = $default;
        }

        return $rps;
    }

    public function delete($key)
    {
        return $this->_connection->delete($key);
    }

    public function deleteMultiple($keys)
    {
        return $this->_connection->deleteMulti($keys);
    }

    public function clear()
    {
        return $this->_connection->flush();
    }

    public function has($key)
    {
        $this->_connection->get($key);
        return Memcached::RES_NOTFOUND != $this->_connection->getResultCode();
    }
}