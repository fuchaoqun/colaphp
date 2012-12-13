<?php
/**
 * use https://github.com/nicolasff/phpredis
 *
 */
class Cola_Ext_Cache_Redis extends Cola_Ext_Cache_Abstract
{
    public $options = array(
        'persistent'            => true,
        'host'                  => '127.0.0.1',
        'port'                  => 6379,
        'timeout'               => 3,
        'ttl'                   => 0,
    );

    public $optionKeys = array(Redis::OPT_SERIALIZER, Redis::OPT_PREFIX);
    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options=array())
    {
        parent::__construct($options);

        $this->conn = new Redis();

        if (empty($this->options['persistent'])) {
            $this->conn->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
        } else {
            $this->conn->pconnect($this->options['host'], $this->options['port'], $this->options['timeout']);
        }

        foreach ($this->optionKeys as $key) {
            if (isset($this->options[$key])) {
                $this->conn->setOption($key, $this->options[$key]);
            }
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
}