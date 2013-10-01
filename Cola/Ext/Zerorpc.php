<?php
class Cola_Ext_Zerorpc
{
    protected $_zmq;

    public $timeout = 1000;

    public $sleep   = 1;

    public $error = array();

    /**
     * Constructor
     *
     * @param string $server like tcp://127.0.0.1:4242
     * @param int $timeout microsecond
     * @param int $sleep microsecond
     */
    public function __construct($server, $timeout = null, $sleep = null)
    {
        $this->_zmq = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_REQ);

        if (!is_null($timeout)) {
            $this->timeout = $timeout;
        }
        if (!is_null($sleep)) {
            $this->sleep = $sleep;
        }

        $this->_zmq->setSockOpt(ZMQ::SOCKOPT_LINGER, $this->timeout);
        $this->_zmq->connect($server);
    }

    /**
     * Call RPC Function
     *
     * @param string $func
     * @param array $args
     * @return mixed
     */
    public function call($func, $args)
    {
        if (!is_array($args)) {
            $this->error = array('code' => -10, '$args must be array');
            return false;
        }

        $msg = $this->_formatRequestMessage($func, $args);
        if (!$this->_send($msg)) {
            return false;
        }

        if ($data = $this->_receive()) {
            return $data[2][0];
        }

        return false;
    }

    /**
     * Send ZeroRPC Request
     *
     * @param string $msg
     * @return boolean
     */
    protected function _send($msg)
    {
        try {
            if ($res = $this->_zmq->send($msg, ZMQ::MODE_DONTWAIT)) {
                return true;
            }
            throw new Exception('rpc send failed.', -1);
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Receive ZeroRPC Response
     *
     * @return mixed
     */
    protected function _receive()
    {
        $times = ceil($this->timeout/$this->sleep);

        for ($i = 0; $i < $times; $i ++) {
            try {
                if ($rps = $this->_zmq->recv(ZMQ::MODE_NOBLOCK)) {
                    return self::unpack($rps);
                }
            } catch (Exception $e) {
                $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
                return false;
            }
            usleep($this->sleep);
        }

        $this->error = array('code' => -2, 'msg' => 'rpc receive timeout.');
        return false;
    }

    /**
     * Format ZeroRPC Request Data
     *
     * @param string $func
     * @param array $args
     * @return string
     */
    protected function _formatRequestMessage($func, $args)
    {
        $data = array(
            array('message_id' => uniqid(''), 'v' => 3),
            $func,
            $args
        );

        return self::pack($data);
    }

    public static function pack($data)
    {
        return msgpack_pack($data);
    }

    public static function unpack($str)
    {
        return msgpack_unpack($str);
    }
}
