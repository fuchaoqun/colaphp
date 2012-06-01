<?php
/**
 *
 */
abstract class Cola_Com_Queue_Abstract
{
    protected $_params = array();

    protected $_name = '_defaultQueue';

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($params = array())
    {
        foreach ($params as $key=>$value) {
            $this->_params[$key] = $value;
        }
        if (isset($this->_params['name'])) {
            $this->name($this->_params['name']);
        }
    }

    /**
     * Set queue name
     *
     * @param string $name
     */
    public function name($name)
    {
        $this->_name = $name;
        return $this;
    }

    abstract public function put($data);

    abstract public function get();
}