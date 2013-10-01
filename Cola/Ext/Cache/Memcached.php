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
    public function set($id, $data, $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->options['ttl'];
        }

        return $this->conn->set($id, $data, $ttl);
    }

    /**
     * Get Cache Data
     *
     * @param mixed $id
     * @return array
     */
    public function get($id)
    {
        return is_array($id) ? $this->conn->getMulti($id) : $this->conn->get($id);
    }
}