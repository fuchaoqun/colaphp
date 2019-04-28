<?php

namespace Cola\Cache\Adapter;

class RedisAdapter extends AbstractAdapter
{
    public $config = [
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

        $func = empty($this->config['servers']) ? '_initSingle' :'_initCluster';
        $this->$func($this->config);
    }

    protected function _initSingle($config)
    {
        $this->conn = new \Redis();

        $func = empty($config['persistent']) ? 'connect' : 'pconnect';

        if (empty($config['unixDomainSocket'])) {
            $this->conn->$func($config['host'], $config['port'], $config['timeout']);
        } else {
            $this->conn->$func($config['unixDomainSocket']);
        }

        if (isset($config['password'])) {
            $this->conn->auth($config['password']);
        }

        foreach ($config['options'] as $key => $val) {
            $this->conn->setOption($key, $val);
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

        $this->conn = new \RedisCluster(...$args);

        foreach ($config['options'] as $key => $val) {
            $this->conn->setOption($key, $val);
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
            $ttl = $this->config['ttl'];
        }

        return (empty($ttl)) ? $this->conn->set($key, $value) : $this->conn->setex($key, $ttl, $value);
    }

    public function setMultiple($values, $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->config['ttl'];
        }

        if (null == $ttl) {
            return $this->conn->mSet($values);
        }

        $multi = $this->conn->multi();
        foreach ($values as $key => $value) {
            $multi = $multi->setex($key, $ttl, $value);
        }

        return $multi->exec();
    }

    /**
     * Get Cache Value
     *
     * @param mixed $key
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $rps = $this->conn->get($key);
        return false === $rps ? $default : $rps;
    }

    public function getMultiple($keys, $default = null)
    {
        $rps = $this->conn->mGet($keys);
        $ret = [];
        foreach ($keys as $idx => $key) {
            $ret[$key] = (false === $rps[$idx]) ? $default : $rps[$idx];
        }

        return $ret;
    }

    public function delete($key)
    {
        return $this->conn->delete($key);
    }

    public function deleteMultiple($keys)
    {
        return $this->conn->delete($keys);
    }

    public function has($key)
    {
        return $this->conn->exists($key);
    }

    public function clear()
    {
        return $this->conn->flushAll();
    }

    /**
     * Put into Queue
     *
     */
    public function qput($name, $value)
    {
        return $this->conn->lPush($name, $value);
    }

    /**
     * Get from queue
     *
     * @param int $timeout >=0 for block, negative for non-blocking
     */
    public function qget($name, $timeout = 0)
    {
        if (0 > $timeout) {
            return $this->conn->rPop($name);
        } else {
            $data = $this->conn->brPop((array)$name, $timeout);
            return isset($data[1]) ? $data[1] : null;
        }
    }
}