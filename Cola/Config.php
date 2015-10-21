<?php
class Cola_Config implements ArrayAccess
{
    /**
     * Contains array of configuration data
     *
     * @var array
     */
    protected $_data = array();

    public $delimiter = '.';

    /**
     * Cola_Config provides a property based interface to
     * an array. The data are read-only unless $allowModifications
     * is set to true on construction.
     *
     * Cola_Config also implements Countable and Iterator to
     * facilitate easy access to the data.
     *
     * @param  array   $data
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->_data = $data;
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name = null, $default = null, $delimiter = '.')
    {
        if (null === $name) {
            return $this->_data;
        }

        if (false === strpos($name, $delimiter)) {
            return isset($this->_data[$name]) ? $this->_data[$name] : $default;
        }

        $name = explode($delimiter, $name);

        $ret = $this->_data;
        foreach ($name as $key) {
            if (!isset($ret[$key])) return $default;
            $ret = $ret[$key];
        }

        return $ret;
    }

    /**
     * Magic function so that $obj->value will work.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    public function set($name, $value, $delimiter = '.')
    {
        $pos = & $this->_data;
        if (!is_string($delimiter) || false === strpos($name, $delimiter)) {
            $key = $name;
        } else {
            $name = explode($delimiter, $name);
            $cnt = count($name);
            for ($i = 0; $i < $cnt - 1; $i ++) {
                if (!isset($pos[$name[$i]])) $pos[$name[$i]] = array();
                $pos = & $pos[$name[$i]];
            }
            $key = $name[$cnt - 1];
        }

        $pos[$key] = $value;


        return $this;
    }

    /**
     * Set if not exists
     *
     */
    public function setnx($name, $value, $delimiter = '.')
    {
        if (is_null($this->get($name, null, $delimiter))) {
            return $this->set($name, $value, $delimiter);
        }

        return $this;
    }

    /**
     * Only allow setting of a property if $allowModifications
     * was set to true on construction. Otherwise, throw an exception.
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws Cola_Exception
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Support isset() overloading on PHP 5.1
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return null !== $this->get($name);
    }

    /**
     * Support unset() overloading on PHP 5.1
     *
     * @param  string $name
     * @throws Cola_Exception
     * @return void
     */
    public function __unset($name)
    {
        $pos = & $this->_data;
        $name = explode($delimiter, $name);
        $cnt = count($name);
        for ($i = 0; $i < $cnt - 1; $i ++) {
            if (!isset($pos[$name[$i]])) return;
            $pos = & $pos[$name[$i]];
        }
        unset($pos);
    }


    /**
     * Defined by Iterator interface
     *
     * @return mixed
     */
    public function keys()
    {
        return array_keys($this->_data);
    }

    /**
     * merge config
     *
     * @param array $config
     * @return Cola_Config
     */
    public function merge($config)
    {
        $this->_data = $this->_merge($this->_data, $config);
        return $this;
    }

    /**
     * merge two arrays
     *
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    protected function _merge($arr1, $arr2)
    {
        foreach($arr2 as $key => $value) {
            if(isset($arr1[$key]) && is_array($value)) {
                $arr1[$key] = $this->_merge($arr1[$key], $arr2[$key]);
            } else {
                $arr1[$key] = $value;
            }
        }
        return $arr1;
    }

    /**
     * ArrayAccess set
     *
     * @param string $offset
     * @param mixed $value
     * @return Cola_Config
     */
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    /**
     * ArrayAccess get
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * ArrayAccess exists
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return null !== $this->get($offset);
    }

    /**
     * ArrayAccess exists
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetUnset($offset)
    {
        return $this->set($offset, null);
    }
}