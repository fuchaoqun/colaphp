<?php
/**
 * use https://github.com/nicolasff/phpredis
 *
 */
class Cola_Ext_Cache_Redis extends Cola_Ext_Cache_Abstract
{
    public $config = array(
        'persistent' => true,
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'timeout'    => 3,
        'ttl'        => 0,
        'options'    => array()
    );

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->conn = new Redis();

        if (empty($this->config['persistent'])) {
            $this->conn->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
        } else {
            $this->conn->pconnect($this->config['host'], $this->config['port'], $this->config['timeout']);
        }

        foreach ($this->config['options'] as $key => $val) {
            $this->conn->setOption($key, $val);
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
            $ttl = $this->config['ttl'];
        }

        if (empty($ttl)) {
            return $this->conn->set($id, $data);
        } else {
            return $this->conn->setex($id, $ttl, $data);
        }
    }

    /**
     * Get Cache Value
     *
     * @param mixed $id
     * @return mixed
     */
    public function get($id)
    {
        if (is_string($id)) {
            return $this->conn->get($id);
        }
        return array_combine($id, $this->conn->mGet($id));
    }

    /**
     * Put into Queue
     *
     */
    public function qput($queue, $value)
    {
        return $this->conn->lPush($queue, $value);
    }

    /**
     * Get from queue
     *
     * @param int $timeout >=0 for block, negative for non-blocking
     */
    public function qget($queue, $timeout = 0)
    {
        if (0 > $timeout) {
            return $this->conn->rPop($queue);
        } else {
            $data = $this->conn->brPop((array)$queue, $timeout);
            return isset($data[1]) ? $data[1] : false;
        }
    }
}