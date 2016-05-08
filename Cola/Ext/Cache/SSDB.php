<?php

require_once COLA_DIR . '/Ext/Cache/drivers/SSDB.php';

class Cola_Ext_Cache_SSDB extends Cola_Ext_Cache_Abstract
{
    public $config = array(
        'host'       => '127.0.0.1',
        'port'       => 8888,
        'timeout'    => 3000,
        'ttl'        => 0,
    );

    protected $_actionMaps = array(
        'get' => 'get', 'mset' => 'multi_set',
        'del' => 'del', 'mdel' => 'multi_del',
        'hset' => 'hset', 'hget' => 'hget', 'hdel' => 'hdel',
        'hexists' => 'hexists', 'hsize' => 'hsize', 'hgetall' => 'hgetall',
        'hmset' => 'multi_hset', 'hmdel' => 'multi_hdel',
        'lpush' => 'qpush_front', 'rpush' => 'qpush_back', 'lrange' => 'qslice',
        'llen' => 'qsize', 'lclear' => 'qclear', 'lpop' => 'qpop_front', 'rpop' => 'qpop_back',
        'substr' => 'substr',
    );

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->conn = new SimpleSSDB($this->config['host'], $this->config['port'], $this->config['timeout']);
    }

    public function set($id, $data, $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->config['ttl'];
        }

        if (empty($ttl)) {
            return $this->conn->set($id, $data);
        } else {
            return $this->conn->setx($id, $data, $ttl);
        }
    }

    public function mget($keys)
    {
        $result = $this->conn->multi_get($keys);
        foreach ($keys as $key) {
            if (!isset($result[$key])) $result[$key] = null;
        }

        return $result;
    }

    public function hkeys($key)
    {
        return $this->conn->hkeys($key, '', '', 0);
    }

    public function hmset($key, $data)
    {
        return false !== $this->conn->multi_hset($key, $data);
    }

    public function hmget($key, $fields)
    {
        $result = $this->conn->multi_hget($key, $fields);
        foreach ($fields as $field) {
            if (!isset($result[$field])) $result[$field] = null;
        }
        return $result;
    }

    public function __call($method, $args)
    {
        if (isset($this->_actionMaps[$method])) {
            return call_user_func_array(array($this->conn, $this->_actionMaps[$method]), $args);
        } else {
            throw new Cola_Exception("Call to undefined method: Cola_Ext_Cache_SSDB::{$method}");
        }
    }
}