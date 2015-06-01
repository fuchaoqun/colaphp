<?php

class Cola_Ext_Cache_Memcached extends Cola_Ext_Cache_Abstract
{
    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options=array())
    {
        parent::__construct($options);

        if (isset($this->options['persistent'])) {
            $this->conn = new Memcached($this->options['persistent']);
        } else {
            $this->conn = new Memcached();
        }

        $this->conn->addServers($this->options['servers']);
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
            $ttl = $this->options['ttl'];
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