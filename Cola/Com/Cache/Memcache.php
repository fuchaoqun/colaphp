<?php

class Cola_Com_Cache_Memcache extends Cola_Com_Cache_Abstract
{
    protected $_connection;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options=array())
    {
        $this->_connection = new Memcache();

        parent::__construct($options);

        foreach ($this->_options['servers'] as $server) {
            $server += array('host' => '127.0.0.1', 'port' => 11211, 'persistent' => true);
            $this->_connection->addServer($server['host'], $server['port'], $server['persistent']);
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

        $this->_connection->set($id, $data, empty($this->_options['compressed']) ? 0 : MEMCACHE_COMPRESSED, $ttl);
    }

    /**
     * Get Cache
     *
     * @param string $key
     * @return mixed
     */
	public function get($id)
	{
		return $this->_connection->get($id);
	}

    /**
     * Delete cache
     * @param string $id
     * @return boolean
     */
    public function delete($key)
    {
        $this->_connection->delete($key);
    }

    /**
     * Increment value
     *
     * @param string $key
     * @param int $value
     */
    public function increment($key, $value = 1)
    {
        $this->_connection->increment($key, $value);
    }

    /**
     * clear cache
     */
    public function clear()
    {
        $this->_connection->flush();
    }

    protected function close()
    {
        $this->_connection->close();
    }

	public function stats()
	{
		return $this->_connection->getStats();
	}
}