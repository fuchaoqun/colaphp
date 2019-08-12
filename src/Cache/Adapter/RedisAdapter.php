<?php

namespace Cola\Cache\Adapter;

use Redis;
use RedisCluster;

class RedisAdapter extends AbstractAdapter
{
    public $_config = [
        'persistent'       => true,
        'host'             => '127.0.0.1',
        'port'             => 6379,
        'unixDomainSocket' => null,
        'timeout'          => 3,
        'ttl'              => null,
        'options'          => []
    ];

    public function __construct($config = array())
    {
        parent::__construct($config);

        $func = empty($this->_config['servers']) ? '_initSingle' :'_initCluster';
        $this->$func($this->_config);
    }

    protected function _initSingle($config)
    {
        $this->_connection = new Redis();

        $func = empty($config['persistent']) ? 'connect' : 'pconnect';

        if (empty($config['unixDomainSocket'])) {
            $this->_connection->$func($config['host'], $config['port'], $config['timeout']);
        } else {
            $this->_connection->$func($config['unixDomainSocket']);
        }

        if (isset($config['password'])) {
            $this->_connection->auth($config['password']);
        }

        foreach ($config['options'] as $key => $val) {
            $this->_connection->setOption($key, $val);
        }
    }

    protected function _initCluster($config)
    {
        if (!isset($config['readTimeout'])) {
            $config['readTimeout'] = $config['timeout'];
        }

        $args = [null, $config['servers'], $config['timeout'], $config['readTimeout']];
        if ($config['persistent']) {
            $args[] = true;
        }
        if ($config['password']) {
            $args[] = $config['password'];
        }

        $this->_connection = new RedisCluster(...$args);

        foreach ($config['options'] as $key => $val) {
            $this->_connection->setOption($key, $val);
        }
    }

    /**
     * Set cache
     *
     * @param string $key
     * @param string $value
     * @param int $ttl
     * @return boolean
     */
    public function set($key, $value, $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->_config['ttl'];
        }

        return (empty($ttl)) ? $this->_connection->set($key, $value) : $this->_connection->setex($key, $ttl, $value);
    }

    public function setMultiple($values, $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->_config['ttl'];
        }

        if (null == $ttl) {
            return $this->_connection->mSet($values);
        }

        $multi = $this->_connection->multi();
        foreach ($values as $key => $value) {
            $multi = $multi->setex($key, $ttl, $value);
        }

        return $multi->exec();
    }

    /**
     * Get Cache Value
     *
     * @param mixed $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $rps = $this->_connection->get($key);
        return false === $rps ? $default : $rps;
    }

    public function getMultiple($keys, $default = null)
    {
        $rps = $this->_connection->mGet($keys);
        $ret = [];
        foreach ($keys as $idx => $key) {
            $ret[$key] = (false === $rps[$idx]) ? $default : $rps[$idx];
        }

        return $ret;
    }

    public function delete($key)
    {
        return $this->_connection->delete($key);
    }

    public function deleteMultiple($keys)
    {
        return $this->_connection->delete($keys);
    }

    public function has($key)
    {
        return $this->_connection->exists($key);
    }

    public function clear()
    {
        return $this->_connection->flushAll();
    }

    public function multi()
    {
        return $this->_connection->multi();
    }
}