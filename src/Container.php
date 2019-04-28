<?php

namespace Cola;

class Container implements \ArrayAccess
{
    protected $_data = [];

    public function set($id, $val)
    {
        $this->_data[$id] = $val;
    }

    public function __set($id, $val)
    {
        $this->set($id, $val);
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new \Exception("No entry was found for {$id} in Container");
        }

        return $this->_data[$id];
    }

    public function __get($id)
    {
        return $this->get($id);
    }

    public function has($id)
    {
        return isset($this->_data[$id]);
    }

    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }
}