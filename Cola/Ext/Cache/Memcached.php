<?php

class Cola_Com_Cache_Memcached extends Cola_Com_Cache_Abstract
{
    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options=array())
    {
        $options += array('ttl' => 900);
        parent::__construct($options);

        if (isset($this->_options['persistent'])) {
            $this->conn = new Memcached($this->_options['persistent']);
        } else {
            $this->conn = new Memcached();
        }

        $this->conn->addServers($this->_options['servers']);
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
            $ttl = $this->_options['ttl'];
        }

        $this->conn->set($id, $data, $ttl);
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