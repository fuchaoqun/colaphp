<?php

namespace Cola\Cache\Adapter;

class MemcachedAdapter extends AbstractAdapter
{
    public function __construct($config = [])
    {
        parent::__construct($config);

        if (isset($this->config['persistent'])) {
            $this->conn = new \Memcached($this->config['persistent']);
        } else {
            $this->conn = new \Memcached();
        }

        $this->conn->addServers($this->config['servers']);
    }

    /**
     * Set cache
     *
     * @param string $key
     * @param string $value
     * @param int $ttl
     * @return boolean
     */
    public function set($key, $val, $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->config['ttl'];
        }

        return $this->conn->set($key, $val, $ttl);
    }

    public function setMultiple($values, $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->config['ttl'];
        }

        return $this->conn->setMulti($values, $ttl);
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
        $rps = $this->conn->get($key);
        return (false === $rps) ? $default : $rps;
    }

    public function getMultiple($keys, $default = null)
    {
        $rps = $this->conn->getMulti($key);
        empty($rps) && $rps = [];
        foreach ($keys as $key) {
            (!isset($rps[$key])) && $rps[$key] = $default;
        }

        return $rps;
    }

    public function delete($key)
    {
        return $this->conn->delete($key);
    }

    public function deleteMultiple($keys)
    {
        return $this->conn->deleteMulti($keys);
    }

    public function clear()
    {
        return $this->conn->flush();
    }

    public function has($key)
    {
        $rps = $this->conn->get($key);
        return Memcached::RES_NOTFOUND != $this->conn->getResultCode();
    }
}