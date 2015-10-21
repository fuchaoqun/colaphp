<?php

class Cola_Ext_Cache_Memcached extends Cola_Ext_Cache_Abstract
{
    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        if (isset($this->config['persistent'])) {
            $this->conn = new Memcached($this->config['persistent']);
        } else {
            $this->conn = new Memcached();
        }

        $this->conn->addServers($this->config['servers']);
    }

    /**
     * Set cache
     *
     * @param string $key
     * @param mixed $value
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

    /**
     * Get Cache Data
     *
     * @param mixed $id
     * @return array
     */
    public function get($key)
    {
        return is_array($key) ? $this->conn->getMulti($key) : $this->conn->get($key);
    }

    public function del($key)
    {
        return $this->conn->delete($key);
    }
}