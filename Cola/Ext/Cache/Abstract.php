<?php
/**
 *
 */
abstract class Cola_Com_Cache_Abstract
{
    public $conn;

    public $_options;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        foreach ($options as $key=>$value) {
            $this->_options[$key] = $value;
        }
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
        return null === $value ? $this->delete($key) : $this->set($key, $value);
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
}