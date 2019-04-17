<?php

namespace Cola\Cache;

/**
 * PSR-16 SimpleCache
 */
abstract class SimpleCache
{
    public $conn;

    public $config = [
        'ttl' => 86400
    ];

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = [])
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
        return $this->delete($key);
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

    public function clear()
    {
        return false;
    }

    abstract public function set($key, $val, $ttl = null);
    abstract public function get($key, $default = null);
    abstract public function setMultiple($values, $ttl = null);
    abstract public function getMultiple($keys, $default = null);
    abstract public function deleteMultiple($keys);
    abstract public function delete($key);
    abstract public function has($key);
}