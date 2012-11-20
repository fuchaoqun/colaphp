<?php

class Cola_Com_Cache_Memcache extends Cola_Com_Cache_Abstract
{
    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options=array())
    {
        $options += array('ttl' => 900);
        $this->conn = new Memcache();

        parent::__construct($options);

        foreach ($this->_options['servers'] as $server) {
            call_user_func_array(array($this->conn, 'addServer'), $server);
        }
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

        $this->conn->set($id, $data, empty($this->_options['compressed']) ? 0 : MEMCACHE_COMPRESSED, $ttl);
    }
}