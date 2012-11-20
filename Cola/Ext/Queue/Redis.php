<?php
/**
 * Cola_Com_Queue_Redis
 *
 * use https://github.com/nicolasff/phpredis
 */
class Cola_Com_Queue_Redis extends Cola_Com_Queue_Abstract
{
    protected $_redis;

    public function __construct($params)
    {
        $params += array('host' => '127.0.0.1', 'port' => 6379, 'timeout' => 0, 'persistent' => true, 'options' => array());
        parent::__construct($params);

        $this->_redis = new Redis();
        $conn = ($this->_params['persistent']) ? 'pconnect' : 'connect';
        $this->_redis->$conn($this->_params['host'], $this->_params['port'], $this->_params['timeout']);
        foreach ($this->_params['options'] as $key => $value) {
            $this->_redis->setOption($key, $value);
        }
    }

    /**
     * Get from queue
     *
     * @param int $timeout >=0 for block, negative for non-blocking
     */
    public function get($timeout = 0)
    {
        if (0 > $timeout) {
            return $this->_redis->rPop($this->_name);
        } else {
            $data = $this->_redis->brPop((array)$this->_name, $timeout);
            return isset($data[1]) ? $data[1] : false;
        }
    }

    public function put($value)
    {
        return $this->_redis->lPush($this->_name, $value);
    }

    public function getMulti($limit, $timeout = 0)
    {
        $ret = array();
        for ($i = 0; $i < $limit; $i ++) {
            $item = $this->get($timeout);
            if (false === $item) break;
            $ret[] = $item;
        }
        return $ret;
    }
}