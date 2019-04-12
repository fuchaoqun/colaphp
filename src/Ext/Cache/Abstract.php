<?php
/**
 *
 */
abstract class Cola_Ext_Cache_Abstract
{
    public $conn;

    public $config = array(
        'ttl' => 86400
    );

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->config = $config + $this->config;
    }

    /**
     * Set cache
     *
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function __set($key, $value)
    {
        return null === $value ? $this->del($key) : $this->set($key, $value);
    }

    /**
     * Get cache
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Delete cache
     *
     * @param string $key
     * @return boolean
     */
    public function __unset($key)
    {
        return $this->del($key);
    }

     /**
     * Magic method
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->conn, $method), $args);
    }
}