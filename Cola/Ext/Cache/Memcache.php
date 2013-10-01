<?php

class Cola_Ext_Cache_Memcache extends Cola_Ext_Cache_Abstract
{
    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options=array())
    {
        parent::__construct($options);

        $this->conn = new Memcache();
        foreach ($this->options['servers'] as $server) {
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
            $ttl = $this->options['ttl'];
        }

        return $this->conn->set($id, $data, empty($this->options['compressed']) ? 0 : MEMCACHE_COMPRESSED, $ttl);
    }
}